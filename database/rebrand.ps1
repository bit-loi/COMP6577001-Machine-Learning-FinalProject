$files = @(
    "c:\xampp\htdocs\shopmart\admin\orders\index.php",
    "c:\xampp\htdocs\shopmart\admin\customers\index.php",
    "c:\xampp\htdocs\shopmart\admin\categories\index.php",
    "c:\xampp\htdocs\shopmart\admin\categories\handler.php",
    "c:\xampp\htdocs\shopmart\admin\simulation.php",
    "c:\xampp\htdocs\shopmart\admin\includes\topnav.php"
)

foreach ($f in $files) {
    if (Test-Path $f) {
        $c = Get-Content $f -Raw
        $c = $c -replace 'Premeditatio Malorum', 'Shopmart'
        $c = $c -replace 'Admin Console', 'Admin Dashboard'
        $c = $c -replace 'Bookstore', 'Shopmart'
        $c = $c -replace '#050505', '#f5f5f5'
        $c = $c -replace '#0a0a0a', '#ffffff'
        $c = $c -replace "Plus Jakarta Sans", "Inter"
        $c = $c -replace "JetBrains Mono", "monospace"
        $c = $c -replace "font-family: 'Playfair Display', serif; font-style: italic;", "font-weight: 800;"
        $c = $c -replace "Playfair Display", "Inter"
        Set-Content $f $c -NoNewline
        Write-Host "Updated: $f"
    } else {
        Write-Host "NOT FOUND: $f"
    }
}
