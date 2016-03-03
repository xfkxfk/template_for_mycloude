#!/usr/bin/perl

use strict;
use warnings;
use utf8;
use feature 'say';
use lib '/secure/Common/src/cpan';

use FindBin;
use lib "$FindBin::Bin/lib";
use POSIX qw/strftime/;
use File::Basename;
use Getopt::Long;
use Data::Dumper;
use Time::HiRes qw(time);
use LWP::UserAgent;
use Cwd;
use JSON;

binmode(STDOUT, ':encoding(utf8)');

my %opts = (
   es        => 'http://127.0.0.1:9200/sniffer/logs/',
   config    => 'http://127.0.0.1/odsa/data/config.json',
   rules     => 'http://127.0.0.1/odsa/data/rules.json',
   interface => 'lo',
   debug     => undef,
   interval  => 60
);
GetOptions (\%opts, 'interface|i=s', 'es|e=s', 'config=s', 'rules=s', 'help|h', 'debug', 'interval|i');

chdir (dirname ($0));

my $ua = LWP::UserAgent->new;
my $cmd = './src/main ' . $opts{interface};
my %portmap;

my $lastrun = 0;
my %config;

&log ("[-] CMD $cmd");

say '[-] Listening on ', $opts{interface};

while (1)
{
    open my $fh, "$cmd |" or die "Couldn't start $cmd: $!";
    while (<$fh>)
    {
        chomp;
        &log ("[-] Sniffer Output: $_") if $_ !~ /^\s*$/;
#        next;

        if (time - $lastrun > $opts{interval})
        {
            my @rules;

            # update config
            my $resp = $ua->get ($opts{config});
            eval {
                %config = %{ decode_json ($resp->decoded_content) };
                say Dumper (@{ $config{monitor_user} });

                # wtf map not working directly ??
                my %dbs   = map { $_ => 1 } @{ $config{monitor_db}   };
                my %users = map { $_ => 1 } @{ $config{monitor_user} };

                $config{monitor_db}   = \%dbs;
                $config{monitor_user} = \%users;

                @rules = @{ $config{sqli} };
            };
            if ($@) {
                say 'ERROR ', $@;
            }

            # update rules
            $resp = $ua->get ($opts{rules});
            eval {
              my $json = decode_json ($resp->decoded_content);
              # say Dumper ($json);
              @rules = (@{ $json->{rules} }, @rules);
              $config{sqli} = join ('|', map { '(' . $_ . ')' } @rules);
            };
            if ($@) {
              say 'ERROR(rules): ', $@;
            }

            say 'Config updated';
            say Dumper (\%config);

            $lastrun = time;
        }

        if (/ID="(\d+)".*TYPE="COM_INIT_DB":\s*(.*)\s*/)
        {
            next unless $portmap{$1};
            $portmap{$1}{db} = $2;
        }
        elsif (/ID="(\d+)" INIT USER="([^"]+)" DB="([^"]+)"/)
        {
            $portmap{$1} = {
                user => $2,
                db   => $3
            };
        }
        elsif (/COM_QUIT ID="(\d+)"/)
        {
            &log ("[-] ID $1 closed");
            delete $portmap{$1};
        }
        elsif (/QUERY_DONE ID="(\d+)"/)
        {
            next unless $portmap{$1};

            my $data = $portmap{$1};
            next unless $data->{timestamp};
            $data->{duration}  = 0 + sprintf ("%.03f", time - $data->{timestamp});
            $data->{timestamp} = int ($data->{timestamp} * 1000);

            # db & user filters
            if (! $config{log_alldb} && $config{db} && ! $config{monitor_db}{ $data->{db} })
            {
                say '[!] Filtered database ', $data->{db};
            }
            elsif (! $config{log_alluser} && $config{user} && ! $config{monitor_user}{ $data->{user} })
            {
                say '[!] Filtered user ', $data->{user};
            }
            else
            {
                my $log = $config{log_normal};
                # record intrusion events
                &log ('[-] SQLi match check');
                if ($data->{phrase} =~ /$config{sqli}/i)
                {
                    &log ('[+] Matched SQLi');
                    push @{ $data->{tags} }, 'SQLi';

                    if ($config{log_intrusion})
                    {
                        $log = 1;
                    }
                }

                # record slow query
                if ($data->{duration} > 1)
                {
                    push @{ $data->{tags} }, 'Slow';
                    if ($config{log_slowquery})
                    {
                        $log = 1;
                    }
                }

                if (! $log)
                {
                    &log ('[!] Ignored query');
                }
                else
                {
                    my $resp = $ua->post ($opts{es}, Content => encode_json ($data));
                    &log ('[+] SEND ', encode_json ($data));
                    &log ('[-] RET  ', $resp->content);
                }
            }

            delete $data->{timestamp};
            $portmap{$1} = $data;
        }
        elsif (/ID="(\d+)" SERVER="([^"]+)" CLIENT="([^"]+)" TYPE="COM_QUERY":\s*(.*)\s*/)
        {
            next unless $portmap{$1};

            my $data = {
                server    => $2,
                client    => $3,
                phrase    => $4,
                timestamp => time,
                tags      => [],
                user      => $portmap{$1}{user},
                db        => $portmap{$1}{db},
            };
            $portmap{$1} = $data;
        }

    }
    close $fh;

    say 'Sniffer crashed, restart in 2s';
    sleep 2;
}

sub log {
    say @_ if $opts{debug};
}
