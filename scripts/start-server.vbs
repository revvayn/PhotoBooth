' Menjalankan MySQL (XAMPP) dan server Laravel secara hidden.
' Aman dipanggil berulang: tidak menyalakan proses ganda jika port sudah aktif.
Option Explicit

Dim ws, fso, root, phpExe, foundPhp, cmd, xamppRoot, mysqlExe, foundNgrok, ex2, candidate

Set ws = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

' Root proyek bisa diberikan sebagai argumen (dipakai Electron).
If WScript.Arguments.Count > 0 Then
    root = WScript.Arguments(0)
Else
    root = fso.GetParentFolderName(fso.GetParentFolderName(WScript.ScriptFullName))
End If

Function PortListening(port)
    Dim e, l
    PortListening = False
    On Error Resume Next
    Set e = ws.Exec("netstat -ano -p tcp")
    If Err.Number = 0 Then
        Do While Not e.StdOut.AtEndOfStream
            l = e.StdOut.ReadLine()
            If InStr(l, ":" & port) > 0 And InStr(l, "LISTENING") > 0 Then
                PortListening = True
            End If
        Loop
    End If
    On Error GoTo 0
End Function

' Cari php di PATH, lalu fallback ke lokasi umum
foundPhp = ""
On Error Resume Next
Dim ex
Set ex = ws.Exec("where php")
If Err.Number = 0 Then
    If Not ex.StdOut.AtEndOfStream Then foundPhp = Trim(ex.StdOut.ReadLine())
End If
On Error GoTo 0

If foundPhp <> "" Then
    phpExe = foundPhp
ElseIf fso.FileExists("C:\xampp\php\php.exe") Then
    phpExe = "C:\xampp\php\php.exe"
ElseIf fso.FileExists("D:\xampp\php\php.exe") Then
    phpExe = "D:\xampp\php\php.exe"
ElseIf fso.FileExists("C:\laragon\bin\php\php.exe") Then
    phpExe = "C:\laragon\bin\php\php.exe"
Else
    phpExe = "php"
End If

' Nyalakan MySQL XAMPP bila port 3306 belum aktif
If Not PortListening(3306) Then
    xamppRoot = ""
    If fso.FileExists("C:\xampp\mysql\bin\mysqld.exe") Then xamppRoot = "C:\xampp"
    If fso.FileExists("D:\xampp\mysql\bin\mysqld.exe") Then xamppRoot = "D:\xampp"
    If xamppRoot = "" And InStr(LCase(phpExe), "\xampp\") > 0 Then
        xamppRoot = Left(phpExe, InStr(LCase(phpExe), "\php\") - 1)
    End If

    If xamppRoot <> "" Then
        mysqlExe = xamppRoot & "\mysql\bin\mysqld.exe"
        If fso.FileExists(mysqlExe) Then
            ws.CurrentDirectory = xamppRoot
            cmd = """" & mysqlExe & """ --defaults-file=""" & xamppRoot & "\mysql\bin\my.ini"" --standalone"
            ws.Run cmd, 0, False
        End If
    End If
End If

' Nyalakan server Laravel bila port 8000 belum aktif
If Not PortListening(8000) Then
    ws.CurrentDirectory = root
    cmd = """" & phpExe & """ """ & root & "\artisan"" serve --host=127.0.0.1 --port=8000"
    ws.Run cmd, 0, False
End If

' Nyalakan ngrok dengan domain statis bila belum aktif (port 4040 = web interface ngrok).
If Not PortListening(4040) Then
    foundNgrok = ""
    On Error Resume Next
    Set ex2 = ws.Exec("where ngrok")
    If Err.Number = 0 Then
        If Not ex2.StdOut.AtEndOfStream Then foundNgrok = Trim(ex2.StdOut.ReadLine())
    End If
    On Error GoTo 0

    If foundNgrok = "" Then
        candidate = ws.ExpandEnvironmentStrings("%LOCALAPPDATA%\Microsoft\WindowsApps\ngrok.exe")
        If fso.FileExists(candidate) Then foundNgrok = candidate
    End If

    If foundNgrok <> "" Then
        ws.CurrentDirectory = root
        cmd = """" & foundNgrok & """ http 8000 --url=https://metalinguistic-unvenerably-fernande.ngrok-free.dev"
        ws.Run cmd, 0, False
    End If
End If
