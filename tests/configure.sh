#!/usr/bin/env bash

cd "$(dirname "${0}")"
BASEDIR="$(pwd)"


markers=()
while read marker; do
	found=false
	for n in "${markers[@]}"; do
		[ "$n" == "$marker" ] && { found=true; break; }
	done

	$found || markers+=("$marker")
done < <(awk <"$BASEDIR/config.ini.template" -F '[ =]+' '$2 ~ /^@.+@$/ {print substr($2,2,length($2)-2)}')


map=
for marker in "${markers[@]}"; do
	# ask for value
	value=
	while [ -z "$value" ]; do
		if [ "${marker/password/}" != "${marker}" ]; then
			read -esp "Provide value for '$marker': " value
			echo
		else
			read -ep "Provide value for '$marker': " value
		fi
	done

	if [ -n "${value//[0-9]/}" ]; then
		value="\\\"${value//\"/\\\\\\\"}\\\""
	fi

	map="${map}M[\"@$marker@\"]=\"$value\";"
done



awk <"$BASEDIR/config.ini.template" >"$BASEDIR/config.ini" -F '[ =]+' \
	'BEGIN{'$map'}{if($2 ~ /^@.+@$/)print $1 " = " M[$2];else print $0}'
