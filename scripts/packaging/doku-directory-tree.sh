#!/bin/bash

set -ex

VERSION=trunk
TEMPDIR="$(date --iso-8601=seconds)-doku-tree-opus4-$VERSION"

./prepare_tarball.sh "$VERSION" "$TEMPDIR"
tree -L 3 -F --dirsfirst "$TEMPDIR" >"$TEMPDIR.tree"

rm -rv "$TEMPDIR/"
rm -rv "$TEMPDIR.tgz"
