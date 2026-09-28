$ErrorActionPreference = 'Stop'

$base = 'F:\Devfihter\FanHub-Plus\resources\views\admin'

$defaults = @{
    "$base\backup\index.blade.php"           = 'Delete this backup file?'
    "$base\roles\index.blade.php"            = 'Delete this role?'
    "$base\tags\index.blade.php"             = 'Delete this tag?'
}

$files = Get-ChildItem -Path $base -Recurse -Filter '*.blade.php' -Force | Where-Object {
    Select-String -Path $_.FullName -Pattern 'confirm\(' -SimpleMatch:$false
}

foreach ($file in $files) {
    $path = $file.FullName
    $content = Get-Content -Raw -LiteralPath $path -Encoding UTF8

    $defaultMsg = $defaults[$path]

    $content = [regex]::Replace(
        $content,
        'onsubmit="return confirm\(\''([^'']+)\''\)"',
        'data-confirm="$1"'
    )

    if ($defaultMsg) {
        $content = [regex]::Replace(
            $content,
            'onsubmit="return confirm\(\)"',
            [regex]::Escape("data-confirm=`"$defaultMsg`"")
        )
    } else {
        $content = [regex]::Replace(
            $content,
            'onsubmit="return confirm\(\)"',
            'data-confirm="Are you sure?"'
        )
    }

    $content = [regex]::Replace(
        $content,
        'onsubmit="return confirm\(\\"([^"\\]+)\\"\)"',
        'data-confirm="$1"'
    )

    $content = [regex]::Replace(
        $content,
        'onclick="return confirm\(\''([^'']+)\''\)"',
        'data-confirm="$1"'
    )

    Set-Content -LiteralPath $path -Value $content -Encoding UTF8 -NoNewline
    Write-Host "Updated: $($file.Name)"
}

Write-Host "Done."
