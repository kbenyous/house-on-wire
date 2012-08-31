#!/usr/bin/perl -w

use vars qw/ %options /; 
use strict;

sub init()
{
        use Getopt::Std;

        getopts("p:i:w:c:",\%options) or usage();
        usage() if not $options{p};
        usage() if not $options{i};
        usage() if not $options{w};
        usage() if not $options{c};
}

sub usage()
{
        print ("Usage : $0 -p <owfs_path> -i <probe_id> -w <warning_state> -c <critical_state>\n");
        exit;
}

&init;

my $STATE_OK=0;
my $STATE_WARNING=1;
my $STATE_CRITICAL=2;
my $STATE_UNKNOWN=3;
my $count=0;

my $id=$options{i};
my $path=$options{p};
my $warning=$options{w};
my $critical=$options{c};
my $temperature=-100;

open (FILEHANDLE, "<$path/$id/temperature") || die "Can't open $path/$id/temperature \n";
#while( defined( my $line = <FILEHANDLE> ) )
while(defined( my $line = <FILEHANDLE> ) )
{
        if ( $line=~ /^\s+([-]?[0-9].*)$/ )
        {
        	$temperature=$1;
        }
}
close FILEHANDLE;

SWITCH: {
	($temperature == -100) && do {
		print "CRITICAL: $temperature est la valeur par defaut. Consultez Zazate la Regexp.";
		exit $STATE_CRITICAL;
		last SWITCH;
	};
	($temperature < $warning) && do {
		print "OK: $temperature < $warning";
		exit $STATE_OK;
		last SWITCH;
	};
	($temperature < $critical)  && do {
		print "WARNING: $temperature > $warning";
		exit $STATE_WARNING;
		last SWITCH;
	};
	($temperature > $critical)  && do {
		print "CRITICAL: $temperature > $critical";
		exit $STATE_CRITICAL;
		last SWITCH;
	};
        (/^.*$/ ) &&  do {
                print ("UNKNOWN: Unknown error.");
                exit $STATE_UNKNOWN;
                last SWITCH;
        };
}
