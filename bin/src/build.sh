set -x
gcc *.c -o main -lpcap -ggdb
strip -s main
