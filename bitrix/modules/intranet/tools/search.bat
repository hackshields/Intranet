@echo off
if not exist %1 echo %1 - no such file or directory exists&goto :EOF
echo Full PathName : %1
echo Short PathName : %~s1
