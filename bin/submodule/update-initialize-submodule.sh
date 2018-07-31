#!/bin/bash

DIR="$( pwd )"
parentdir="$(dirname "$DIR")"

list_submodule_with_info=$(git config --file .gitmodules --get-regexp path | egrep ^submodule | awk '{ print $1,$2 }' | cut -d ' ' -f 1)

for submodule_all in $list_submodule_with_info ; do

    submodule_name=$(echo $submodule_all| cut -d'.' -f 2)
    submodule_url=$(git config -f .gitmodules --get submodule."$submodule_name".url)
    submodule_branch=$(git config -f .gitmodules --get submodule."$submodule_name".branch)
    submodule_path=$(git config -f .gitmodules --get submodule."$submodule_name".path | cut -d'.' -f 2)

    # test if directory with submodule_name exist inside DIR/.git/modules/
    if [ -d "$DIR/.git/modules/$submodule_name" ]; then
        rm -rf $DIR/.git/modules/$submodule_name
    fi

    # if the name = path
    if [ -d "$DIR/.git/modules/$submodule_path" ]; then
        rm -rf $DIR/.git/modules/$submodule_path
    fi

    # test if directory exist inside DIR/$submodule_path
    if [ -d "$DIR/$submodule_path" ]; then
        rm -rf $DIR/$submodule_path
    fi

    # I see if I have the  submodule name
    if [ -f "$submodule_name" ]
    then
        #I delete in index the submodule name
        git rm -r -f --cached $submodule_name || true
        # or I delete the submodule path if there is residue before
        git rm -r -f --cached $submodule_path || true
    else
        # I delete the submodule path
        git rm -r -f --cached $submodule_path || true
    fi

    git submodule add -b $submodule_branch --force --name $submodule_name $submodule_url $submodule_path

    if [ -f "$submodule_name" ]
    then
        (git reset HEAD $submodule_name) || true
    else
        (git reset HEAD $submodule_path) || true
    fi

done


