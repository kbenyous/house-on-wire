#!/usr/bin/perl

# Home On Wire

use Config::IniHash;
use DateTime;
use DBI;
use vars qw/ %options /;

sub init ()
{
        use Getopt::Std;
        getopts("f:",\%options) or usage();
        usage() if not $options{f};
}
sub usage ()
{
  if ( ! -f '/etc/house-on-wire/house-on-wire.ini')
  {
          print ("Usage : $0 -f <path_to_config_file>\nDefault config file /etc/house-on-wire/house-on-wire.ini not found");
    exit (1);
  }
}  

&init;

if (exists $options{f}) 
{
  $config_file=$options{f};
}
else 
{
  $config_file='/etc/house-on-wire/house-on-wire.ini';
}

my $cfg = ReadINI "$config_file";
my $database=$cfg->{bdd}->{dbname};
my $hostname=$cfg->{bdd}->{host};
my $login=$cfg->{bdd}->{username};
my $password=$cfg->{bdd}->{password};
my $dbport=$cfg->{bdd}->{port};

my $owfs_path=$cfg->{owfs}->{path};


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
  
  if(exists($SIMPLES_DEVICES{$sonde_type}))
  {
     $sonde_data = `cat $owfs_path/$sonde/$SIMPLES_DEVICES{$sonde_type}`;
  }
  else
  {
#    if($sonde eq "26.2FAE60010000")
    if($sonde eq "26.24AE60010000")
    {
      my $vdd = `cat $owfs_path/$sonde/VDD`;
      my $vad = `cat $owfs_path/$sonde/VAD`;
      $sonde_data = $vad * 100 / $vdd;
    }

  }
 
  chomp($sonde_type);
  chomp($sonde_data);
  #print "$datetime $sonde $sonde_type $sonde_data \n";

  $dbi->do("insert into onewire_data (date, id, value) values ('$datetime', '$sonde', '$sonde_data')");
}
$dbi->disconnect;
