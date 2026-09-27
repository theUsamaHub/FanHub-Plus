$ErrorActionPreference = 'Stop'

$base = 'F:\Devfihter\FanHub-Plus\resources\views\admin'

# Per-file default confirm messages when the existing pattern is `confirm()` (no message).
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

    # 1) onsubmit="return confirm('...')"  -> drop onsubmit, add data-confirm
    $content = [regex]::Replace(
        $content,
        'onsubmit="return confirm\(\''([^'']+)\''\)"',
        'data-confirm="$1"'
    )

    # 2) onsubmit="return confirm()"  -> drop onsubmit, add data-confirm with default message
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

    # 3) onsubmit="return confirm(\"...\")"  (escaped double quotes) -> data-confirm="..."
    $content = [regex]::Replace(
        $content,
        'onsubmit="return confirm\(\\"([^"\\]+)\\"\)"',
        'data-confirm="$1"'
    )

    # 4) onclick="return confirm('...')" on a button -> data-confirm on the button
    $content = [regex]::Replace(
        $content,
        'onclick="return confirm\(\''([^'']+)\''\)"',
        'data-confirm="$1"'
    )

    Set-Content -LiteralPath $path -Value $content -Encoding UTF8 -NoNewline
    Write-Host "Updated: $($file.Name)"
}

Write-Host "Done."
