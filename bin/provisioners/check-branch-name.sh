#!/usr/bin/env bash
#
# Tests the branch name as it must be "master", "develop" or prefixed with one of the following:
# - "feature/.+"
# - "release/.+"
# - "hotfix/.+".
#
# It can also accept branch name respecting semantic version system with last digit unknown (i.e. 1.x or 10.20.x).
#

set -o errexit
set -o pipefail
set -o nounset
#set -o xtrace # Uncomment this to debug this script.

# The branch name must be passed as an argument.
if [ $# != 1 ] 
then
    echo -e "\e[31mFailure!\e[39m This script must take only 1 argument, $# given."
    exit 255
fi

branchName=${1:-}

# Tests "master" or "develop".
if [ "${branchName}" = "master" ] || [ "${branchName}" = "develop" ]
then
    # "master" or "develop" are ok.
    exit 0
fi

# Tests prefixes.
if [[ "${branchName}" =~ ^(feature|release|hotfix)/.+ ]]
then
    # Those prefixes are correct.
    exit 0
fi

# Tests semantic version system.
#if [[ "${branchName}" =~ ^([0-9]+\.){1,2}x$ ]]
if [[ "${branchName}" =~ ^(v|vtest)([0-9])+\.([0-9])+\.([0-9])+$ ]]
then
    # semantic version system is correct.
    exit 0
fi

echo -e "\e[31mFailure!\e[39m The branch name \"${branchName}\" does not satisfy the requirements."
echo -e "\e[34mExamples:\e[39m 'master', 'develop', 'feature/*', 'release/*', 'hotfix/*', 'v0.x' or 'v1.0.x'."
exit 1

