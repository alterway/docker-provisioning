#!/bin/bash

# US-ASCII -- is -- a subset of UTF-8 => US-ASCII files are encoded in UTF-8
ENCODING=$(file -bi $1); echo $1-$ENCODING
