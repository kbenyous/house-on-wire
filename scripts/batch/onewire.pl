#!/usr/bin/perl

# Home On Wire

use Config::IniHash;
use DateTime;
use DBI;
use vars qw/ %options /;

sub init ()
{
        use Getopt::Std;
        getopts("hdf:",\%options) or usage();
        usage() if $options{h};
}
sub usage ()
{
   print STDERR << "EOF";
    This program does...
    usage: $0 [-hd] [-f config_file]
     -h        : this (help) message
     -d        : print debugging messages to stderr
     -f file   : Alternative config file ( Default /etc/house-on-wire/house-on-wire.ini )
    example: $0 -d -f file
EOF
        exit;
}  

&init;

if (exists $options{f}) 
{
  $config_file=$options{f};
}
else 
{
  if ( ! -f '/etc/house-on-wire/house-on-wire.ini')
  {
	usage();
  }
  else
  {
    $config_file='/etc/house-on-wire/house-on-wire.ini';
  }
}

my $cfg = ReadINI "$config_file";
my $database=$cfg->{bdd}->{dbname};
my $hostname=$cfg->{bdd}->{host};
my $login=$cfg->{bdd}->{username};
my $password=$cfg->{bdd}->{password};
my $dbport=$cfg->{bdd}->{port};

my $owfs_path=$cfg->{owfs}->{path}.'/uncached';


$maintenant = DateTime->now();
$maintenant->set_time_zone( 'Europe/Paris' );
$datetime = ($maintenant->date()." ".$maintenant->time());

my %SIMPLES_DEVICES = (
    "DS18B20"   =>  'temperature',
    "DS18B22"   =>  'temperature',
    "DS18S20"   =>  'temperature',
    "DS1822"    =>  'temperature'
);


opendir(THERM,$owfs_path) or die "no";
my @sondes = grep /^..\..*/, readdir THERM;

my $dbi=DBI->connect("DBI:Pg:dbname=$database;host=$hostname;port=$dbport","$login","$password") or die "Erreur pendant l'ouverture de la base de Donnée PG $DBI::errstr";

# Récupération de la température de chaque capteur
foreach (@sondes) {
  my $sonde=$_;
  my $sonde_type = `cat $owfs_path/$sonde/type`;
  chomp($sonde_type);  
 
  if (exists $options{d})
  {
    print "$sonde $sonde_type \n";
  }

 
  # On met en place un traitement spécial pour la 2438 qui est utilisé pour l'humidité, la luminosité, etc
  if($sonde_type eq "DS2438")
  {
    # Lecture de la température presente dans le DS2438
    $sonde_data = `cat $owfs_path/$sonde/temperature`;
    chomp($sonde_data);
    sauvegarder_ligne($dbi, $datetime, $sonde.".t", $sonde_type, $sonde_data );

    #Traitement des data en fonction de l'utilisation de la sonde
    my $prep = $dbi->prepare('SELECT type FROM onewire WHERE id = \''.$sonde.'.v\'') or die $dbi->errstr; 
    $prep->execute() or die "Echec requête\n"; 
    while ( my ($type) = $prep->fetchrow_array ) 
    { 
      my $vdd = `cat $owfs_path/$sonde/VDD`;
      my $vad = `cat $owfs_path/$sonde/VAD`;
      
      if($vad ne "" && $vdd ne "" && $vdd != 0 && $vdd ne "")
{
      if($type eq "luminosite")
      {
        $sonde_data = $vad * 100 / $vdd;
      }
      elsif($type eq "humidite")
      {
        $sonde_data = $vad * 33.33;
      }
      elsif($type eq "pression")
      {
        $sonde_data = ((($vad/$vdd)+0.095)/0.009)*10;

      }
      elsif($type eq "niveau_eau")
      { 
        $sonde_data = ((($vad/$vdd)+0.04)/0.09)/0.0980638;
      }
        chomp($sonde_data);
        sauvegarder_ligne($dbi, $datetime, $sonde.".v", $sonde_type, $sonde_data );
}
else
{
 print "Erreur de recuperation des valeurs \n";
}
    } 
    $prep->finish(); 
  }
  elsif($sonde_type eq "DS2406")
  {
    #Traitement des data en fonction de l'utilisation de la sonde
    my $prep = $dbi->prepare('SELECT type FROM onewire WHERE id = \''.$sonde.'\'') or die $dbi->errstr;
    $prep->execute() or die "Echec requête\n";
    while ( my ($type) = $prep->fetchrow_array )
    {
      my $pio = `cat $owfs_path/$sonde/PIO.A`;
      my $latch = `cat $owfs_path/$sonde/latch.A`;
      my $sensed = `cat  $owfs_path/$sonde/sensed.A`;

      if($type eq "ouverture")
      {
        $sonde_data = $sensed;
      }
      elsif($type eq "humidite")
      {
      
      }
        chomp($sonde_data);
        sauvegarder_ligne($dbi, $datetime, $sonde, $sonde_type, $sonde_data );
    }
    $prep->finish();


  }
  else
  {
    if(exists($SIMPLES_DEVICES{$sonde_type}))
    {
       $sonde_data = `cat $owfs_path/$sonde/$SIMPLES_DEVICES{$sonde_type}`;
       chomp($sonde_data);
       sauvegarder_ligne($dbi, $datetime, $sonde, $sonde_type, $sonde_data );
    }
  }
}
$dbi->disconnect;

# Procédure pour sauvegarder en base de donnees
sub sauvegarder_ligne { 
  my ( $dbi, $datetime, $sonde, $sonde_type, $sonde_data ) = @_; 
 
  if (exists $options{d})
  {
    print "$datetime $sonde $sonde_type $sonde_data \n";
  }
  else
  {
   if($sonde_data ne "")
   {
	$dbi->do("insert into onewire_data (date, id, value) values ('$datetime', '$sonde', '$sonde_data')");
   }  
}
  return; 
} 
