#!/bin/bash

echo -e "Content-type: text/html\n\n\n"

echo -n "Files: "
find -name '*php' -type f | wc -l
echo "Line count:"
find -name '*php' -type f | xargs wc -l
files=`find -name '*php' -type f | wc -l`
lines=`find -name '*php' -type f | xargs wc -l | grep -i total | sed 's/total//g'`

echo Average lines per file: $(( $lines / $files ))
