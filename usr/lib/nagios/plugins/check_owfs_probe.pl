#!/usr/bin/perl -w

use vars qw/ %options /; 
use strict;

sub init()
{
        use Getopt::Std;

        getopts("n:p:",\%options) or usage();
        usage() if not $options{n};
        usage() if not $options{p};

}

sub usage()
{
        print ("Usage : $0 -n <probe_number> -p <path>\n");
        exit;
}

&init;

my $STATE_OK=0;
my $STATE_WARNING=1;
my $STATE_CRITICAL=2;
my $STATE_UNKNOWN=3;
my $count=0;

my $path=$options{p};
my $pnumber=$options{n};

opendir(THERM,"$path") or die "no such file or directory";
my @probes = grep /^[0-9][0-9]\..*/, readdir THERM;

 
foreach (@probes) {
	$count++;
}

SWITCH: {
	($pnumber == $count) && do {
		print "OK: $count probes found. Expected $pnumber.";
		exit $STATE_OK;
		last SWITCH;
	};
	($pnumber > $count)  && do {
		print "CRITICAL: $count probes found. Expected $pnumber.";
		exit $STATE_CRITICAL;
		last SWITCH;
	};
	($pnumber < $count)  && do {
		print "CRITICAL: $count probes found. Expected $pnumber.";
		exit $STATE_CRITICAL;
		last SWITCH;
	};
}
