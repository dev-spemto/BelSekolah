Set WshShell = CreateObject("WScript.Shell")
Set FSO = CreateObject("Scripting.FileSystemObject")

' 1. Set Working Directory ke folder tempat VBS berada secara otomatis
CurrentDir = FSO.GetParentFolderName(WScript.ScriptFullName)
WshShell.CurrentDirectory = CurrentDir

' 2. Path PHP Portable
PhpExecutable = CurrentDir & "\php\php.exe"

' Pastikan PHP Portable ditemukan sebelum menjalankan server
If Not FSO.FileExists(PhpExecutable) Then
    MsgBox "File PHP Portable tidak ditemukan di: " & vbCrLf & PhpExecutable, 16, "Error Bel Sekolah"
    WScript.Quit
End If

' 3. Pastikan file .env ada
If Not FSO.FileExists(CurrentDir & "\.env") Then
    If FSO.FileExists(CurrentDir & "\.env.example") Then
        FSO.CopyFile CurrentDir & "\.env.example", CurrentDir & "\.env"
    End If
End If

' 4. Pastikan folder & file database.sqlite ada
DbFolder = CurrentDir & "\database"
If Not FSO.FolderExists(DbFolder) Then FSO.CreateFolder(DbFolder)
DbFile = DbFolder & "\database.sqlite"

NeedMigration = False
If Not FSO.FileExists(DbFile) Then
    Set CreateDb = FSO.CreateTextFile(DbFile, True)
    CreateDb.Close
    NeedMigration = True
End If

' 5. Jalankan Migrasi jika file database baru dibuat
If NeedMigration Then
    CmdMigrate = "cmd /c """"" & PhpExecutable & """ artisan migrate --force"""
    WshShell.Run CmdMigrate, 0, True
    
    CmdKeyGen = "cmd /c """"" & PhpExecutable & """ artisan key:generate --force"""
    WshShell.Run CmdKeyGen, 0, True
End If

' 6a. Jalankan PHP Artisan Serve di Port 8085
CmdToRun = "cmd /c """"" & PhpExecutable & """ artisan serve --host=127.0.0.1 --port=8085"""
WshShell.Run CmdToRun, 0, False

' 6b. Jalankan Laravel Schedule Worker di Background (Untuk Auto Close & Auto Shutdown)
CmdSchedule = "cmd /c """"" & PhpExecutable & """ artisan schedule:work"""
WshShell.Run CmdSchedule, 0, False

' 7. LOOP PENGECEKAN KONEKSI (Menunggu Server Siap Sebelum Buka Browser)
TargetURL = "http://127.0.0.1:8085"
Set HTTP = CreateObject("MSXML2.ServerXMLHTTP.6.0")

ServerReady = False
MaxRetries = 15 ' Maksimal mencoba 15 kali (sekitar 15 detik)

For i = 1 To MaxRetries
    WScript.Sleep 1000 ' Tunggu 1 detik tiap iterasi
    On Error Resume Next
    HTTP.Open "GET", TargetURL, False
    HTTP.Send ""
    
    ' Jika status HTTP merespons (misal Status 200/302/500), berarti port 8085 sudah siap
    If Err.Number = 0 Then
        ServerReady = True
        On Error GoTo 0
        Exit For
    End If
    On Error GoTo 0
Next

' 8. Buka Browser setelah Server Benar-Benar Siap
On Error Resume Next
Dim browserPath
browserPath = ""

' Cek Google Chrome (System & User Level)
browserPath = WshShell.RegRead("HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe\")
If browserPath = "" Then browserPath = WshShell.RegRead("HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe\")

' Cek Microsoft Edge jika Chrome tidak ada
If browserPath = "" Then browserPath = WshShell.RegRead("HKLM\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\msedge.exe\")
If browserPath = "" Then browserPath = WshShell.RegRead("HKCU\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\msedge.exe\")

' Eksekusi pembukaan browser
If browserPath <> "" Then
    WshShell.Run """" & browserPath & """ --app=" & TargetURL, 1, False
Else
    WshShell.Run TargetURL, 1, False
End If