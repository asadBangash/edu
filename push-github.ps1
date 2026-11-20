# Run once from: D:\laragon\www\edu-x
# Right-click PowerShell -> Run as yourself (not admin required)

Set-Location $PSScriptRoot

git config http.postBuffer 2147483648
git config http.version HTTP/1.1
git config http.lowSpeedLimit 0
git config http.lowSpeedTime 999999

Write-Host "Local pack size:"
git count-objects -vH

Write-Host "`nPushing main to origin..."
git push -u origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "`nHTTPS push failed. Try SSH:" -ForegroundColor Yellow
    Write-Host "  1. Create key: ssh-keygen -t ed25519 -C `"your_email`""
    Write-Host "  2. Add public key at https://github.com/settings/keys"
    Write-Host "  3. git remote set-url origin git@github.com:asadBangash/edu.git"
    Write-Host "  4. git push -u origin main"
    exit $LASTEXITCODE
}

Write-Host "`nVerify remote:"
git ls-remote origin main