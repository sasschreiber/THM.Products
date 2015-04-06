#!/bin/bash

cd /var/www
# Change logfile path here
LOGFILE="/var/www/Data/Logs/THM.Products/benchmark_log.txt"

if [ -f $LOGFILE ]
then
  rm $LOGFILE
fi
mkdir -p "$(dirname "$LOGFILE")" && touch "$LOGFILE"

echo "Starting Benchmark..."

# Flush all caches beforce benchmarking and remove standard output
./flow flow:cache:flush --force > /dev/null

# Clean database
echo "Clearing database..."
./flow benchmark:cleandb

######## 
# First test: Flat hierarchy - no subproducts or properties
echo "Running test #1 (Flat)..."
printf "\n\n########### Flat Hierarchy Test ###########\n\n" >> $LOGFILE
./flow benchmark:write --productsCount 5000 --propertiesPerProduct 0 --childrenDepth 0 --childrenLength 0 >> $LOGFILE
printf "\n\n" >> $LOGFILE

#Run findAll
# (Add more tests here)
./flow benchmark:findAll >> $LOGFILE

#Cleanup
echo "Clearing database..."
./flow benchmark:cleandb
./flow flow:cache:flush --force > /dev/null



######## 
# Second test: Many subproducts (references) but no properties
echo "Running test #2 (Referencing)..."
printf "\n\n########### Referencing Test ###########\n\n" >> $LOGFILE
./flow benchmark:write --productsCount 1000 --propertiesPerProduct 0 --childrenDepth 4 --childrenLength 1 >> $LOGFILE
printf "\n\n" >> $LOGFILE

#Run findAll
# (Add more tests here)
./flow benchmark:findAll >> $LOGFILE

#Cleanup
echo "Clearing database..."
./flow benchmark:cleandb
./flow flow:cache:flush --force > /dev/null

echo "All tests done."
