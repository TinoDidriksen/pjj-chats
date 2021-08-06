#!/usr/bin/perl
use warnings;
use strict;
use utf8;
use IO::Socket;
use IO::Select;

# Set the input terminator to a zero byte string, pursuant to the
# protocol in the flash documentation.
#$/ = "\0";

# Create a new socket, on port 4120
my $lsn = new IO::Socket::INET(Listen => 1, 
							LocalPort => 4120,
						    Reuse => 1,
						    Proto => 'tcp' )
   or die ("Couldn't start server: $!");

# Create an IO::Select handler
my $sel = new IO::Select( $lsn );

# Close filehandles

close(STDIN);
close(STDOUT);

my $xdpf = `cat crossdomain.xml`;
my %registry = ();
my %fh2chat = ();

warn "Server ready.  Waiting for connections . . . \n";

# Enter into while loop, listening to the handles that are available.
while( my @read_ready = $sel->can_read ) {
	my @data;

	foreach my $fh (@read_ready) {

		# Create a new socket
		if ($fh == $lsn) {
			my $new = $lsn->accept;
			$sel->add($new);
			#push( @data, "SERVER: User (" . fileno($new) . ") has joined.");
			warn "Connection from " . $new->peerhost . ".\n";
		}

		# Handle connection
		else {
			my $input = "";
			$fh->recv($input, 1024);
			if ($input) {
				$input =~ s/\r//g;
				$input =~ s/^\s+//g;
				$input =~ s/\s+$//g;
				my @lns = split /[\n\0]/, $input;
				foreach my $line (@lns) {
					$line =~ s/\n//g;
					$line =~ s/\0//g;
					$line =~ s/^\s+//g;
					$line =~ s/\s+$//g;
					warn "Input: $line \n";
					if ($line eq '<policy-file-request/>') {
						warn "Output: $xdpf \n";
						print $fh "$xdpf\0";
					}
					elsif ($line =~ m/^REGISTER (\d+)$/i) {
						if (!$registry{$1}) {
							$registry{$1} = ();
						}
						$registry{$1}{fileno($fh)} = $fh;
						$fh2chat{fileno($fh)} = $1;
						warn "Socket ".fileno($fh)." registered for chat $1\n";
					}
					elsif ($line =~ m/^POST (\d+)$/i) {
						if ($fh2chat{fileno($fh)} && $fh2chat{fileno($fh)} == $1) {
							while (my ($fn,$fo) = each(%{$registry{$1}})) {
								if ($fo && $fo != $fh) {
									warn "Refreshing chat $1 for socket $fn\n";
									print $fo "REFRESH $1\0";
								}
							}
						}
					}
					elsif ($line =~ m/^KEEPALIVE/i) {
						print $fh "KEEPALIVE ".time()."\0";
					}
				}
			}
			else {
				warn "Disconnection from " . $fh->peerhost . ".\n";
				if ($fh2chat{fileno($fh)}) {
					my $chat = $fh2chat{fileno($fh)};
					undef $registry{$chat}{fileno($fh)};
					warn "Socket ".fileno($fh)." unregistered for chat $chat\n";
				}
				$sel->remove($fh);
				$fh->close;
			}
		}
	}

=pod
	# Write to the clients that are available
	foreach my $fh ( my @write_ready = $sel->can_write(0) ) {
		foreach my $line (@data) {
			warn "OUTPUT: $line \n";
			print $fh "$line\0";
		}
	}
=cut

	undef @data;
}

warn "Server ended.\n";
