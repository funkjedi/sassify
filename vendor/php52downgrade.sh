#!/bin/sh

#
# Replaces all PHP 5.3 code with PHP 5.2 compatible code
#

find scssphp -type f -name '*.php' | xargs sed -i '' -E 's|PHP_VERSION, .5\.3.|PHP_VERSION, "5.2"|g'
find scssphp -type f -name '*.php' | xargs sed -i '' -E 's|__DIR__|dirname(__FILE__)|g'
find scssphp -type f -name '*.php' | xargs sed -i '' -E 's|namespace .*;|/* & */|g'
find scssphp -type f -name '*.php' | xargs sed -i '' -E 's|use .*;|/* & */|g'
find scssphp -type f -name '*.php' | xargs sed -i '' -E 's|class ([^\\]*) extends \\Leafo\\ScssPhp\\Formatter\\|class \1 extends Leafo_ScssPhp_Formatter_|g'
find scssphp -type f -name '*.php' | xargs sed -i '' -E 's|class ([^\\]*) extends \\Leafo\\ScssPhp\\|class \1 extends Leafo_ScssPhp_|g'
find scssphp/src -type f -name '*.php' | xargs sed -i '' -E 's|class ([^\\]*) extends Formatter|class Formatter_\1 extends Leafo_ScssPhp_Formatter|g'
find scssphp/src -type f -name '*.php' | xargs sed -i '' -E 's|class ([^\\]*)|class Leafo_ScssPhp_\1|g'
find scssphp/src -type f -name '*.php' | xargs sed -i '' -E 's|Leafo\\ScssPhp\\Formatter\\|Leafo_ScssPhp_Formatter_|g'
find scssphp/src -type f -name '*.php' | xargs sed -i '' -E 's|Leafo\\ScssPhp\\|Leafo_ScssPhp_|g'
find scssphp/src -type f -name '*.php' | xargs sed -i '' -E 's|new ([A-Z])|new Leafo_ScssPhp_\1|g'
find scssphp/src -type f -name '*.php' | xargs sed -i '' -E 's|\[Parser|[Leafo_ScssPhp_Parser|g'
