#!/usr/bin/env python3
"""
Convert all remaining `onsubmit="return confirm('<msg>')"` patterns in admin
blade files to `data-confirm="<msg>"`. Handles messages that contain embedded
single quotes (e.g. `{{ __('Are you sure?') }}`).
"""
import os
import re
from pathlib import Path

ADMIN_DIR = Path(r"F:\Devfihter\FanHub-Plus\resources\views\admin")

# Map filename -> default confirm message (for empty confirm() calls
# that need a sensible message).
DEFAULTS = {
    "backup/index.blade.php": "Delete this backup file?",
    "roles/index.blade.php": "Delete this role?",
    "tags/index.blade.php": "Delete this tag?",
}

# Match onsubmit="return confirm(...)" — capture the full argument so it
# works with both quoted literals and balanced Blade translation tags.
ON_SUBMIT_PATTERN = re.compile(
    r'onsubmit="return confirm\((?P<arg>.*?)\)"'
)

def transform_confirm_arg(arg: str, filename: str) -> str:
    """Extract the human-readable message from inside confirm(arg)."""
    arg = arg.strip()
    # Strip surrounding single quotes: '...'
    if arg.startswith("'") and arg.endswith("'") and len(arg) >= 2:
        inner = arg[1:-1]
    elif arg.startswith('"') and arg.endswith('"') and len(arg) >= 2:
        inner = arg[1:-1]
    elif arg == "":
        return DEFAULTS.get(filename, "Are you sure?")
    else:
        inner = arg
    return inner


def transform_one_form_line(line: str, filename: str) -> str:
    """Replace onsubmit=...confirm(...) with data-confirm= on the same form tag.
    We keep the leading `<form ...>` part intact and append data-confirm="..."."""
    m = ON_SUBMIT_PATTERN.search(line)
    if not m:
        return line

    message = transform_confirm_arg(m.group("arg"), filename)
    # Replace onsubmit attribute with data-confirm.
    new_line = ON_SUBMIT_PATTERN.sub(
        f'data-confirm="{message}"',
        line,
        count=1
    )
    return new_line


def transform_one_button_line(line: str, filename: str) -> str:
    """Replace onclick=...confirm(...) on a button with data-confirm."""
    pattern = re.compile(r'onclick="return confirm\((?P<arg>.*?)\)"')
    m = pattern.search(line)
    if not m:
        return line
    message = transform_confirm_arg(m.group("arg"), filename)
    return pattern.sub(f'data-confirm="{message}"', line, count=1)


def process_file(path: Path) -> bool:
    rel = path.relative_to(ADMIN_DIR).as_posix()
    original = path.read_text(encoding="utf-8")
    new_lines = []
    changed = False
    for line in original.splitlines(keepends=True):
        new_line = transform_one_form_line(line.rstrip("\n"), rel)
        new_line = transform_one_button_line(new_line, rel)
        if new_line != line.rstrip("\n"):
            changed = True
        new_lines.append(new_line + "\n")
    if changed:
        path.write_text("".join(new_lines), encoding="utf-8")
    return changed


def main() -> None:
    targets = [
        p for p in ADMIN_DIR.rglob("*.blade.php")
        if "confirm(" in p.read_text(encoding="utf-8")
    ]
    for path in targets:
        if process_file(path):
            print(f"Updated: {path.relative_to(ADMIN_DIR).as_posix()}")


if __name__ == "__main__":
    main()
