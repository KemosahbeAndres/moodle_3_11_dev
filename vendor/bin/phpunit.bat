@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/phpunit
SET COMPOSER_RUNTIME_BIN_DIR=%~dp0
D:\moodle\server\php\php "%BIN_TARGET%" %*