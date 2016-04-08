#!/usr/bin/env bash

# prompt for username, if required
echo "OPUS requires a dedicated system account under which Solr will be running."
echo "In order to create this account, you will be prompted for some information."

while [ -z "$OPUS_USER_NAME" ]; do
	[[ -z $OPUS_USER_NAME ]] && read -p "System Account Name [opus4]: " OPUS_USER_NAME
	if [ -z "$OPUS_USER_NAME" ]; then
	  OPUS_USER_NAME='opus4'
	fi
	OPUS_USER_NAME_ESC=`echo "$OPUS_USER_NAME" | sed 's/\!/\\\!/g'`

	if getent passwd "$OPUS_USER_NAME" &>/dev/null; then
		echo "Selected user account exists already."
		read -p "Use it anyway? [N] " choice
		case "${choice,,}" in
			"y"|"yes"|"j"|"ja")
				CREATE_OPUS_USER=N
				;;
			*)
				OPUS_USER_NAME=
		esac
	fi
done

# create user account
[[ -z $CREATE_OPUS_USER ]] && CREATE_OPUS_USER=Y
if [ "$CREATE_OPUS_USER" = Y ];
then
  if [ "$OS" = ubuntu ]
  then
    useradd -c 'OPUS 4 Solr manager' --system "$OPUS_USER_NAME_ESC"
  else
    useradd -c 'OPUS 4 Solr manager' --system --create-home --shell /bin/bash "$OPUS_USER_NAME_ESC"
  fi
fi

# preparing OWNER string for chown-calls.
OPUS_GROUP_NAME="`id -gn "$OPUS_USER_NAME"`"
OWNER="$OPUS_USER_NAME:$OPUS_GROUP_NAME"

