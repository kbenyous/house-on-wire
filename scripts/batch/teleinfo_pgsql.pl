#!/usr/bin/perl

# Home On Wire

use Device::SerialPort;
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

my $port = Device::SerialPort->new("/dev/teleinfo");
$port->databits($cfg->{teleinfo_serial}->{databits});
$port->baudrate($cfg->{teleinfo_serial}->{baudrate});
$port->parity($cfg->{teleinfo_serial}->{parity});
$port->stopbits($cfg->{teleinfo_serial}->{stopbits});
$port->read_char_time (1); #Evite le CPU load 100

my $line='';
my $spacecount=0;

while(1) {
	my $byte=$port->read(1);
	$spacecount++ if ($byte eq chr(32)); #Detection caractere  SPACE
	if (($byte) and ($byte eq chr(2))) #Detection caractere Caractere STX
	{
		$line='';
		$maintenant = DateTime->now();
		$maintenant->set_time_zone( 'Europe/Paris' );
		$datetime = ($maintenant->date()." ".$maintenant->time().".".$maintenant->millisecond());
	}
	if (($byte) and ($byte eq chr(3))) # Detection caractere Caractere ETX
	{
		if ($line =~ /ADCO (\d{12});OPTARIF (....);ISOUSC (\d{2});HCHC (\d{9});HCHP (\d{9});PTEC (..)..;IINST (\d{3});IMAX (\d{3});PAPP (\d{5});HHPHC (.);MOTDETAT (\d{6});$/)
		{
			print "Format ligne OK : $line $datetime\n";

			my $dbi=DBI->connect("DBI:Pg:dbname=$database;host=$hostname;port=$dbport","$login","$password") or die "Erreur pendant l'ouverture de la base de Donnée PG $DBI::errstr";
            		$dbi->do("insert into teleinfo (isousc,hchc,hchp,ptec,iinst,imax,papp,date) values ($3,$4,$5,'$6'::varchar(2),$7,$8,$9, now())");
			$dbi->disconnect;
			
			$my_file="/tmp/papp";
			open(PLOT,">$my_file") || die("The file cannot be opened!");
			print PLOT "$9";
			close(PLOT);
		}
		else
		{
			print "Format ligne NOK : $line\n";
		}
	} 
	if ($byte eq chr(13)) #Detection caractere Carriage Return
	{
		$line="$line".';';
		$spacecount=0;
	}
	elsif (($byte ne chr(10)) and ($spacecount < 2)) #Detection Caractere LineFeed
  	{	
		$line="$line".$byte;
	}
}

