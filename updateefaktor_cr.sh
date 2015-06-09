#!/bin/bash
# Shell script to make datadir
# Author: poodll


EFAKTORDIR=/home/chipmunkyou/efaktormoodle
DEVDIR=/home/chipmunkyou/public_html/moodle/cr/mod/completionreset
NEWDIR=${EFAKTORDIR}/mod
echo "gitting down efaktor"
cd ${EFAKTORDIR}
git pull
echo "copying mod"
rsync -av --exclude=".*" ${DEVDIR} ${NEWDIR}
echo "copying done"
echo "gitting up"
git add *
git commit -a
git push
echo "gitting done"
echo "all done"
