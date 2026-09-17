[Setup]
AppName=Bel Sekolah Otomatis by SPEMTO
AppVersion=1.0
AppPublisher=Tim IT SMP Muhammadiyah Tonjong
AppPublisherURL=https://smpmuhtonjong.sch.id
AppSupportURL=https://smpmuhtonjong.sch.id
AppContact=085185033377 / smpmuhitonjong@gmail.com
AppCopyright=© Tim IT SMP Muhammadiyah Tonjong

; === BYPASS ADMIN ACCESS & AUTO DUAL-ARCH (32-BIT / 64-BIT) ===
PrivilegesRequired=lowest
ArchitecturesInstallIn64BitMode=x64compatible
DefaultDirName={userpf}\Spemto_BelSekolahOtomatis
DefaultGroupName=Bel Sekolah Otomatis by SPEMTO

OutputDir=OutputInstaller
OutputBaseFilename=Setup_Bel_Sekolah_Otomatis_SPEMTO_v1.0
Compression=lzma2/ultra64
SolidCompression=yes
SetupIconFile=C:\project\BelSekolah\app.ico
UninstallDisplayIcon={app}\app.ico

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked
Name: "autostart"; Description: "Jalankan Bel Sekolah Otomatis saat Windows Startup (Rekomendasi)"; GroupDescription: "Pengaturan Startup:"

[Files]
; Menyalin seluruh project (termasuk PHP, SQLite Database, & File Audio Sound)
Source: "C:\project\BelSekolah\*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs

[Icons]
Name: "{group}\Bel Sekolah Otomatis"; Filename: "{code:GetBrowserPath}"; Parameters: "{code:GetBrowserParams}"; IconFilename: "{app}\app.ico"
Name: "{group}\Buka di Browser Default"; Filename: "http://127.0.0.1:8000"; IconFilename: "{app}\app.ico"
Name: "{group}\{cm:UninstallProgram,Bel Sekolah Otomatis}"; Filename: "{uninstallexe}"

Name: "{autodesktop}\Bel Sekolah Otomatis"; Filename: "{code:GetBrowserPath}"; Parameters: "{code:GetBrowserParams}"; IconFilename: "{app}\app.ico"; Tasks: desktopicon
Name: "{userstartup}\BelSekolahRunner"; Filename: "{app}\run_background.vbs"; Tasks: autostart

[Run]
; Matikan instance PHP runner lama jika ada sebelum menjalankan yang baru
Filename: "taskkill.exe"; Parameters: "/F /IM php.exe"; Flags: runhidden

; Jalankan server di background dan buka antarmuka browser
Filename: "{app}\run_background.vbs"; Description: "Jalankan Server Bel Sekolah sekarang"; Flags: postinstall shellexec runhidden
Filename: "{code:GetBrowserPath}"; Parameters: "{code:GetBrowserParams}"; Description: "Buka Dashboard Bel Sekolah"; Flags: postinstall shellexec skipifsilent

[UninstallRun]
; Hentikan proses latar belakang PHP dan WScript saat uninstall dilakukan
Filename: "taskkill.exe"; Parameters: "/F /IM php.exe"; Flags: runhidden; RunOnceId: "StopPhpProcessOnUninstall"
Filename: "taskkill.exe"; Parameters: "/F /IM wscript.exe"; Flags: runhidden; RunOnceId: "StopWscriptProcessOnUninstall"

[UninstallDelete]
; PEMBERSIHAN TOTAL: Hapus seluruh isi direktori {app} dan direktori itu sendiri
Type: filesandordirs; Name: "{app}\storage\framework\views\*"
Type: filesandordirs; Name: "{app}\storage\framework\cache\*"
Type: filesandordirs; Name: "{app}\storage\framework\sessions\*"
Type: filesandordirs; Name: "{app}\*"
Type: dirifempty; Name: "{app}"

[Code]
var
  DetectedBrowserPath: String;
  IsAppModeSupported: Boolean;

function DetectBestBrowser(): String;
var
  Path: String;
begin
  // 1. Google Chrome (Registry HKLM / HKCU & Common Program Files)
  if RegQueryStringValue(HKEY_LOCAL_MACHINE, 'SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe', '', Path) or
     RegQueryStringValue(HKEY_CURRENT_USER, 'SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe', '', Path) then
  begin
    if FileExists(Path) then begin IsAppModeSupported := True; Result := Path; Exit; end;
  end;

  if FileExists(ExpandConstant('{commonpf64}\Google\Chrome\Application\chrome.exe')) then begin IsAppModeSupported := True; Result := ExpandConstant('{commonpf64}\Google\Chrome\Application\chrome.exe'); Exit; end;
  if FileExists(ExpandConstant('{commonpf32}\Google\Chrome\Application\chrome.exe')) then begin IsAppModeSupported := True; Result := ExpandConstant('{commonpf32}\Google\Chrome\Application\chrome.exe'); Exit; end;
  if FileExists(ExpandConstant('{userappdata}\Google\Chrome\Application\chrome.exe')) then begin IsAppModeSupported := True; Result := ExpandConstant('{userappdata}\Google\Chrome\Application\chrome.exe'); Exit; end;
  if FileExists(ExpandConstant('{localappdata}\Google\Chrome\Application\chrome.exe')) then begin IsAppModeSupported := True; Result := ExpandConstant('{localappdata}\Google\Chrome\Application\chrome.exe'); Exit; end;

  // 2. Microsoft Edge (App Mode Supported)
  if RegQueryStringValue(HKEY_LOCAL_MACHINE, 'SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\msedge.exe', '', Path) or
     RegQueryStringValue(HKEY_CURRENT_USER, 'SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\msedge.exe', '', Path) then
  begin
    if FileExists(Path) then begin IsAppModeSupported := True; Result := Path; Exit; end;
  end;

  if FileExists(ExpandConstant('{commonpf32}\Microsoft\Edge\Application\msedge.exe')) then begin IsAppModeSupported := True; Result := ExpandConstant('{commonpf32}\Microsoft\Edge\Application\msedge.exe'); Exit; end;
  if FileExists(ExpandConstant('{commonpf64}\Microsoft\Edge\Application\msedge.exe')) then begin IsAppModeSupported := True; Result := ExpandConstant('{commonpf64}\Microsoft\Edge\Application\msedge.exe'); Exit; end;

  // 3. Mozilla Firefox
  if RegQueryStringValue(HKEY_LOCAL_MACHINE, 'SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\firefox.exe', '', Path) or
     RegQueryStringValue(HKEY_CURRENT_USER, 'SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\firefox.exe', '', Path) then
  begin
    if FileExists(Path) then begin IsAppModeSupported := False; Result := Path; Exit; end;
  end;

  // 4. Fallback Default Browser
  IsAppModeSupported := False;
  Result := 'http://127.0.0.1:8000';
end;

function InitializeSetup(): Boolean;
begin
  DetectedBrowserPath := DetectBestBrowser();
  Result := True;
end;

function GetBrowserPath(Param: String): String;
begin
  Result := DetectedBrowserPath;
end;

function GetBrowserParams(Param: String): String;
begin
  if IsAppModeSupported then
    Result := '--app=http://127.0.0.1:8000'
  else begin
    if DetectedBrowserPath = 'http://127.0.0.1:8000' then 
      Result := '' 
    else 
      Result := 'http://127.0.0.1:8000';
  end;
end;

// FORCE PURGE FOLDER APABILA MASIH ADA SISA FILE SEMENTARA / TERKUNCI
procedure CurUninstallStepChanged(CurUninstallStep: TUninstallStep);
var
  AppDir: String;
begin
  if CurUninstallStep = usPostUninstall then
  begin
    AppDir := ExpandConstant('{app}');
    if DirExists(AppDir) then
    begin
      DelTree(AppDir, True, True, True);
    end;
  end;
end;