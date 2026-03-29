Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd.exe /c start_engines.bat", 0, False
Set WshShell = Nothing