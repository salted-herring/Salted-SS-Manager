# ###########################################
# deploy-script.sh
# ----------------
# deployment script for salted herring sites.
# Performs the following tasks:
# 1. Backup current db.
# 2. Checks out latest code.
# 3. Backs up current site.
# 4. Synchs with composer & bower
# 5. Updates db with current state.
# ###########################################

#
# Script vars. Set these up prior to running.
#
SITE_ROOT=`realpath $REP_SITE_ROOT`
DEFAULT_BRANCH="production"
HTDOCS_DIR="$REP_HTDOCS_DIR"
SQL_DUMPS_DIR="$REP_SQL_DIR"
REPO_DIR=`realpath $REP_REPO_DIR`
VERSIONS_DIR=`realpath $REP_VERSIONS_DIR`
MYSQL_HOST="$REP_MYSQL_HOST"
MYSQL_USER="$REP_MYSQL_USER"
MYSQL_DATABASE="$REP_MYSQL_TABLE"
VERSION_NAME=$VERSIONS_DIR/$(date "+%Y-%m-%d-%H_%M_%S")
WWWUSER="$REP_WWWUSER"

# ##########################################
# YOU SHOULDN'T HAVE TO EDIT BELOW HERE.
# ##########################################

#root_required if [ $USER != 'root' ]; then
#root_required     echo -e "\e[31mTHIS SCRIPT MUST BE RUN AS ROOT\e[39m"
#root_required     exit
#root_required fi

echo "Which environment would you like to deploy?"
echo "1. Lite (only file updates)"
echo "2. Full (file, composer, and bower)"
read userchoice

case $userchoice in
1) echo "Lite mode chosen"
    MODE="lite"
    ;;
2) echo "Full mode chosen"
    MODE="full"
    ;;
*) echo "Default to development"
    MODE="lite"
    ;;
esac

read -p "Which branch should we deploy from?: [$DEFAULT_BRANCH] " branch

if [[ -z "$branch" ]]; then
   printf '%s\n' "Deployment branch: $DEFAULT_BRANCH"
   branch=$DEFAULT_BRANCH
else
   printf 'Deployment branch: %s\n' "$branch"
fi

if [ -d "$SITE_ROOT/$HTDOCS_DIR/maintenance-mode" ]; then
    echo -e "\e[32mEntering Maintenance mode\e[39m"
    cd $SITE_ROOT/$HTDOCS_DIR
    sake dev/tasks/MaintenanceMode on
fi

mkdir -p $SITE_ROOT/$SQL_DUMPS_DIR
mysqldump -h $MYSQL_HOST -u $MYSQL_USER -p'$REP_MYSQL_PASS' $MYSQL_DATABASE > $SITE_ROOT/$SQL_DUMPS_DIR/$MYSQL_DATABASE-$(date "+%b_%d_%Y_%H_%M_%S").sql

if [ -t 1 ]; then echo -e "\e[32mMySQL dump successful\e[39m"; fi
cd $REPO_DIR
git fetch --all
git checkout $branch
git pull origin $branch
if [ -t 1 ]; then echo -e "\e[32mPulled from $branch branch\e[39m"; fi

if [ $MODE == "full" ];
then
    echo -e "\e[32mUpdating composer... \e[39m";
    composer update;
    echo -e "\e[32mComposer updated. Now bower... \e[39m";
    cd themes/default;
    bower update;
    echo -e "\e[32mBower updated.\e[39m";
fi

if [ -t 1 ]; then echo -e "\e[32mPreparing to depreciate the current public_html\e[39m"; fi
cd $SITE_ROOT
cp -rf $REPO_DIR $VERSION_NAME
chown -R $WWWUSER:$WWWUSER $VERSION_NAME
if [ -t 1 ]; then echo -e "\e[32mCurrent public_html has been depreciated\e[39m"; fi
rm -rf $SITE_ROOT/$HTDOCS_DIR;
ln -s $VERSION_NAME $SITE_ROOT/$HTDOCS_DIR
cd $SITE_ROOT/$HTDOCS_DIR
if [ -t 1 ]; then echo -e "\e[32mCreating symbolic link to assets directory...\e[39m"; fi
rm -rf $SITE_ROOT/$HTDOCS_DIR/assets
ln -s $SITE_ROOT/assets .
if [ -t 1 ]; then echo -e "\e[32mRefreshing database\e[39m"; fi


cd $SITE_ROOT/$HTDOCS_DIR

if [ $MODE == "full" ];
then
    sake dev/build flush=all;
else
    sake dev/build;
fi
if [ -t 1 ]; then echo -e "\e[32mDatabase refreshed\e[39m"; fi
if [ -t 1 ]; then echo -e "\e[32mCleaning...\e[39m"; fi
rm -rf .git*
rm .editorconfig

if [ $DEFAULT_BRANCH != 'production' ] && [ $DEFAULT_BRANCH != 'production' ]; then
    rm -rf robots.txt;
    echo "User-agent: *" > robots.txt;
    echo "Disallow: /" >> robots.txt;
fi
if [ -d "$SITE_ROOT/$HTDOCS_DIR/maintenance-mode" ]; then
    echo -e "\e[32mExiting Maintenance mode\e[39m"
    cd $SITE_ROOT/$HTDOCS_DIR
    sake dev/tasks/MaintenanceMode off
fi

cd $SITE_ROOT
if [ -t 1 ]; then echo -e "\e[32mDeployment successful\e[39m"; fi

