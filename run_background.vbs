Set WshShell = CreateObject("WScript.Shell")
' Angka 0 di akhir menandakan perintah dijalankan secara tersembunyi (Hidden Window)
WshShell.Run "cmd /c ""C:\project\BelSekolah\start_server.bat""", 0, False