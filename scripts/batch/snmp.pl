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

$maintenant = DateTime->now();
$maintenant->set_time_zone( 'Europe/Paris' );
$datetime = ($maintenant->date()." ".$maintenant->time());

my $dbi=DBI->connect("DBI:Pg:dbname=$database;host=$hostname;port=$dbport","$login","$password") or die "Erreur pendant l'ouverture de la base de Donnée PG ".$dbi::errstr;

# Lecture des sondes à checker dans la base de données
my $sth = $dbi->prepare('SELECT * FROM snmp') or die "Couldn't prepare statement: " . $dbi->errstr;
$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;

# Read the matching records and print them out          
while (@data = $sth->fetchrow_array()) 
{
  my $snmp_id = $data[0];
  my $snmp_command = $data[2];
  my $snmp_data = `$snmp_command`;
  chomp($snmp_data);
  print "\t$datetime $snmp_id: $snmp_command $snmp_data\n";
  if($snmp_data ne '')
  {
    $dbi->do("insert into onewire_data (date, id, value) values ('$datetime', '$snmp_id', '$snmp_data')");
  }

}
#if ($sth->rows == 0) { print "No names matched `$lastname'.\n\n";}
$sth->finish;
$dbi->disconnect;
