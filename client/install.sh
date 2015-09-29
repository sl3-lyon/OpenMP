#!/bin/bash

#usage : ./install.sh -a gccAndDeps -f gcc-1.2.0 -t gcc
#usage : -a or -archive is archive name without .tar.gz
#usage : -f or -linkfrom is name of package directory
#usage : -t or -linkto is desired symlink name

ARCHIVE_NAME=""
LINKFROM=""
LINKTO=""

while [[ $# > 1 ]]
do
	key="$1"

	case $key in
		-a|--archive)
		ARCHIVE_NAME="$2"
		shift # past argument
		;;
		-f|--linkfrom)
		LINKFROM="$2"
		shift # past argument
		;;
		-t|--linkto)
		LINKTO="$2"
		shift # past argument
		;;
		*)
		# unknown option
		;;
	esac
	shift # past argument or value

done

if [ "$ARCHIVE_NAME" == "" || "$LINKFROM" == "" || "$LINKTO" == "" ]; then (
    echo ERROR: Failed to parse arguments or missing argument(s) 1>&2
    exit 1 # terminate and indicate error
) fi

cd "$(dirname "$0")"

tar zxvf $ARCHIVE_NAME.tar.gz package -C /usr/bin
tar zxvf $ARCHIVE_NAME.tar.gz lib -C /lib

ln -s /usr/bin/$LINKFROM $LINKTO

exit 0