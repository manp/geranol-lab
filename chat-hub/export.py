#!/usr/bin/env python3
"""export.py — ساخت client-conversations.md از دیتای geranol-chat
استفاده: python3 export.py [DATA_DIR] [خروجی.md]
خروجی: جمع‌بندی هر گفتگو (بدون دادهٔ حساس) برای تحلیل Charter/بک‌لاگ."""
import json
import os
import re
import sys
from collections import OrderedDict

data_dir = sys.argv[1] if len(sys.argv) > 1 else os.path.join(os.path.dirname(__file__), 'data')
out_path = sys.argv[2] if len(sys.argv) > 2 else os.path.join(os.path.dirname(__file__), 'client-conversations.md')

dialogues_dir = os.path.join(data_dir, 'dialogues')
if not os.path.isdir(dialogues_dir):
    sys.exit('no dialogues dir: ' + dialogues_dir)

# موبایل احتمالی در متن = ماسک (مشتری اگر شماره داد، در export پنهان می‌شود)
phone = re.compile(r'(?<!\d)(09\d{9})(?!\d)')


def mask(t: str) -> str:
    return phone.sub(r'09xxxxxxxxx', t)


def block(path: str) -> str:
    with open(path, encoding='utf-8') as f:
        d = json.load(f)
    sid = os.path.basename(path).replace('.json', '')
    lines = [f'## گفتگو `{sid[:8]}`',
             f"- آغاز: {d.get('created', '?')} — آخرین: {d.get('updated', '?')} — نوبت‌ها: {len(d.get('turns', []))}",
             '']
    for turn in d.get('turns', []):
        who = '**مشتری:**' if turn['role'] == 'user' else '**دستیار:**'
        lines.append(f'- {who} {mask(turn["content"]).replace(chr(10), " / ")}')
    lines.append('')
    return '\n'.join(lines)


files = sorted(f for f in os.listdir(dialogues_dir) if f.endswith('.json'))
parts = ['# گفتگوهای مشتری — پروژهٔ گرانول',
         '',
         f'تعداد گفتگوها: {len(files)} — منبع: geranol-chat.nsar.ir (خودکار از دیتای چت‌هاب)',
         '',
         'ورودی تحلیل: منشور نسخهٔ ۱ + بک‌لاگ؛ بستن Issueهای #6/#7 پس از جمع‌بندی.',
         '']
for f in files:
    parts.append(block(os.path.join(dialogues_dir, f)))

# یادداشت‌های آزاد مشتری
notes_path = os.path.join(data_dir, 'client-notes.jsonl')
if os.path.exists(notes_path):
    parts += ['## یادداشت‌های آزاد مشتری', '']
    with open(notes_path, encoding='utf-8') as f:
        for line in f:
            try:
                n = json.loads(line)
                parts.append(f'- ({n["ts"][:10]}) {mask(n["note"])}')
            except Exception:
                continue
    parts.append('')

with open(out_path, 'w', encoding='utf-8') as f:
    f.write('\n'.join(parts))
print('wrote', out_path, '-', len(files), 'dialogues')
