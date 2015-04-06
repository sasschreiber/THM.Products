#!/bin/bash
FLOW_DB=flow
FLOW_USER=root
FLOW_PASS=root

#cd /var/www
# Change logfile path here
DATE=$(date +"%Y%m%d%H%M%S")
LOGFILE=`pwd`"/Data/Logs/THM.Products/benchmark_log_"$DATE".txt"

mkdir -p "$(dirname "$LOGFILE")" && touch "$LOGFILE"

echo "Starting Benchmark..."

# Flush all caches beforce benchmarking and remove standard output
./flow flow:cache:flush --force > /dev/null

# Clean database
echo "Clearing database..."
mysql -u $FLOW_USER -p$FLOW_PASS -e "DROP DATABASE $FLOW_DB"
mysql -u $FLOW_USER -p$FLOW_PASS -e "CREATE DATABASE flow CHARACTER SET utf8 COLLATE utf8_general_ci"
./flow doctrine:create

######## 
# First test: Flat hierarchy - no subproducts or properties
echo "Running test #1 (Flat)..."
printf "\n\n########### Flat Hierarchy Test ###########\n\n" >> $LOGFILE
./flow benchmark:write --productsCount 500 --propertiesPerProduct 0 --childrenDepth 0 --childrenLength 0 --productsPerFlush 30 >> $LOGFILE
printf "\n\n" >> $LOGFILE

#Run readAllTopLevelProducts
# (Add more tests here)
./flow benchmark:readAllTopLevelProducts >> $LOGFILE

#Cleanup
echo "Clearing database..."
mysql -u $FLOW_USER -p$FLOW_PASS -e "DROP DATABASE $FLOW_DB"
mysql -u $FLOW_USER -p$FLOW_PASS -e "CREATE DATABASE flow CHARACTER SET utf8 COLLATE utf8_general_ci"
./flow doctrine:create
./flow flow:cache:flush --force > /dev/null



######## 
# Second test: Many subproducts (references) but no properties
echo "Running test #2 (Referencing)..."
printf "\n\n########### Referencing Test ###########\n\n" >> $LOGFILE
./flow benchmark:write --productsCount 100 --propertiesPerProduct 0 --childrenDepth 4 --childrenLength 1 --productsPerFlush 30 >> $LOGFILE
printf "\n\n" >> $LOGFILE

#Run readAllTopLevelProducts
# (Add more tests here)
./flow benchmark:readAllTopLevelProducts >> $LOGFILE

#Cleanup
echo "Clearing database..."
mysql -u $FLOW_USER -p$FLOW_PASS -e "DROP DATABASE $FLOW_DB"
mysql -u $FLOW_USER -p$FLOW_PASS -e "CREATE DATABASE $FLOW_DB CHARACTER SET utf8 COLLATE utf8_general_ci"
./flow doctrine:create
./flow flow:cache:flush --force > /dev/null

######## 
# Third test: Many properties, embedded in the couch case
echo "Running test #3 (Embedded properties)..."
printf "\n\n########### Property Test ###########\n\n" >> $LOGFILE
./flow benchmark:write --productsCount 500 --propertiesPerProduct 5 --childrenDepth 0 --childrenLength 0 --productsPerFlush 30 >> $LOGFILE
printf "\n\n" >> $LOGFILE

#Run readAllTopLevelProducts
# (Add more tests here)
./flow benchmark:readAllTopLevelProducts >> $LOGFILE

#Cleanup
echo "Clearing database..."
mysql -u $FLOW_USER -p$FLOW_PASS -e "DROP DATABASE $FLOW_DB"
mysql -u $FLOW_USER -p$FLOW_PASS -e "CREATE DATABASE flow CHARACTER SET utf8 COLLATE utf8_general_ci"
./flow doctrine:create
./flow flow:cache:flush --force > /dev/null

######## 
# Fourth test: Combined case
echo "Running test #4 (Combination)..."
printf "\n\n########### Combination Test ###########\n\n" >> $LOGFILE
./flow benchmark:write --productsCount 100 --propertiesPerProduct 5 --childrenDepth 4 --childrenLength 1 --productsPerFlush 30 >> $LOGFILE
printf "\n\n" >> $LOGFILE

#Run readAllTopLevelProducts
# (Add more tests here)
./flow benchmark:readAllTopLevelProducts >> $LOGFILE

#Cleanup
echo "Clearing database..."
mysql -u $FLOW_USER -p$FLOW_PASS -e "DROP DATABASE $FLOW_DB"
mysql -u $FLOW_USER -p$FLOW_PASS -e "CREATE DATABASE flow CHARACTER SET utf8 COLLATE utf8_general_ci"
./flow doctrine:create
./flow flow:cache:flush --force > /dev/null

echo "All tests done."


