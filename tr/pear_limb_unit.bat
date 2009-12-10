@echo off

set PHPBIN="@PHP-BIN@"
"@PHP-BIN@" "@PHP-DIR@/limb/tests_runner/bin/limb_unit.php" %*
