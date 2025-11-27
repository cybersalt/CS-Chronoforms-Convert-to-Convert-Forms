@echo off
echo Building CF6 to Convert Forms Joomla Component Package...

REM Change to the directory containing this script
cd /d "%~dp0"

REM Create output directory if it doesn't exist
if not exist "dist" mkdir dist

REM Remove old zip if exists
if exist "dist\com_cf6convert.zip" del "dist\com_cf6convert.zip"

REM Create the ZIP file
cd com_cf6convert
powershell -Command "Compress-Archive -Path * -DestinationPath '..\dist\com_cf6convert.zip' -Force"
cd ..

echo.
echo Package created: dist\com_cf6convert.zip
echo.
echo To install:
echo 1. Go to your Joomla admin panel
echo 2. Extensions ^> Manage ^> Install
echo 3. Upload the com_cf6convert.zip file
echo.
pause
