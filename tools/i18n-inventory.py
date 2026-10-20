#!/usr/bin/env python3
"""i18n inventory: which term is used by which pages / template sets."""
import csv
import json
import re
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
LANG_FILE = ROOT / "lang" / "Localization_en.json"
JSON_RENDERER = ROOT / "src" / "Output" / "JsonRenderer.php"
TEMPLATE_SETS = ["default", "bootstrap2", "bootstrap5", "twigged", "twigged5"]
OUT_CSV = ROOT / "tools" / "i18n-inventory.csv"
OUT_DETAIL = ROOT / "tools" / "i18n-usage-by-file.csv"
OUT_MD = ROOT / "tools" / "i18n-inventory.md"

PAGE_EXCLUSIVE = {
    "shelves.html": "shelves",
    "customize.html": "customize",
    "filters.html": "filter (isFilterPage)",
    "about.html": "about",
    "recent.html": "recent",
    "bookdetail.html": "book",
}


def parse_complete_map():
    text = JSON_RENDERER.read_text(encoding="utf-8")
    pat = re.compile(r'"([A-Za-z0-9_]+)"\s*=>\s*\$this->localize\(\s*"([^"]+)"\s*\)')
    return dict((m.group(1), m.group(2)) for m in pat.finditer(text))


def scan_templates():
    usage = defaultdict(lambda: defaultdict(list))
    pat = re.compile(r"(?:it\.c\.i18n\.|it\[.c.\]\[.i18n.\]\.)([A-Za-z0-9_]+)")
    pat2 = re.compile(r"(?<![\w.])c\.i18n\.([A-Za-z0-9_]+)")
    for tset in TEMPLATE_SETS:
        tdir = ROOT / "templates" / tset
        if not tdir.is_dir():
            continue
        for f in sorted(tdir.rglob("*.html")):
            try:
                lines = f.read_text(encoding="utf-8", errors="replace").splitlines()
            except OSError:
                continue
            for i, line in enumerate(lines, 1):
                for m in list(pat.finditer(line)) + list(pat2.finditer(line)):
                    usage[m.group(1)][tset].append((str(f.relative_to(ROOT)), i))
    return usage


def scan_js():
    usage = defaultdict(list)
    pat_get = re.compile(r"getI18n\(\s*['\"]([^'\"]+)['\"]")
    pat_c = re.compile(r"(?:currentData\.c\.i18n|data\.c\.i18n|c\.i18n)\.([A-Za-z0-9_]+)")
    pat_cur = re.compile(r"currentData\.c\.i18n\[['\"]([^'\"]+)['\"]\]")
    roots = [ROOT / "util.js", ROOT / "resources", ROOT / "templates"]
    files = []
    for r in roots:
        if r.is_file():
            files.append(r)
        elif r.is_dir():
            files += [p for p in r.rglob("*.js") if ".yarn" not in str(p)]
    for f in sorted(set(files)):
        try:
            lines = f.read_text(encoding="utf-8", errors="replace").splitlines()
        except OSError:
            continue
        rel = str(f.relative_to(ROOT))
        for i, line in enumerate(lines, 1):
            for m in pat_get.finditer(line):
                usage[m.group(1)].append((rel, i))
            for m in pat_c.finditer(line):
                usage[m.group(1)].append((rel, i))
            for m in pat_cur.finditer(line):
                usage[m.group(1)].append((rel, i))
    for k in list(usage):
        seen = set()
        uniq = []
        for item in usage[k]:
            if item not in seen:
                seen.add(item)
                uniq.append(item)
        usage[k] = uniq
    return usage


def scan_server():
    usage = defaultdict(list)
    pat = re.compile(r"localize\(\s*[\"']([^\"']+)[\"']")
    for sub in ["src/Pages", "src/Output", "src/Calibre", "src/Model"]:
        d = ROOT / sub
        if not d.is_dir():
            continue
        for f in sorted(d.rglob("*.php")):
            try:
                lines = f.read_text(encoding="utf-8", errors="replace").splitlines()
            except OSError:
                continue
            rel = str(f.relative_to(ROOT))
            mcls = re.search(r"Page([A-Za-z]+)\.php$", f.name)
            hint = mcls.group(1) if mcls else ""
            for i, line in enumerate(lines, 1):
                for m in pat.finditer(line):
                    usage[m.group(1)].append((rel, i, hint))
    return usage


def guess_dot(camel, lang_keys):
    """Heuristic camel -> dot: shelfImportTitle -> shelf.importTitle (marked with * if guessed)."""
    m = re.match(r"^([a-z]+)([A-Z].*)$", camel)
    if not m:
        return ""
    prefix, rest = m.group(1), m.group(2)
    rest = rest[0].lower() + rest[1:]
    candidate = f"{prefix}.{rest}"
    if candidate in lang_keys:
        return candidate + "*"
    return ""


def md_cell(value, code=False):
    """Escape a value for use inside a markdown table cell."""
    text = "" if value is None else str(value)
    text = text.replace("|", "\\|").replace("\n", "<br/>")
    if code or text in ("?",) or "*" in text:
        return f"`{text}`"
    return text


def page_guard_for_template(relpath):
    name = Path(relpath).name
    if name in PAGE_EXCLUSIVE:
        return PAGE_EXCLUSIVE[name]
    if name in ("main.html", "page.html", "header.html", "footer.html", "base.html"):
        return "global chrome + it.page branches"
    return "shared"

def main():
    with open(LANG_FILE, encoding="utf-8") as fh:
        lang_keys = json.load(fh)
    camel2dot = parse_complete_map()
    tpl_usage = scan_templates()
    js_usage = scan_js()
    srv_usage = scan_server()
    all_camel = set(camel2dot) | set(tpl_usage) | set(js_usage)
    all_rows = []
    for camel in sorted(all_camel):
        dot = camel2dot.get(camel, "") or guess_dot(camel, lang_keys)
        in_c = "Y" if camel in camel2dot else "N"
        tpl_cols = {}
        for tset in TEMPLATE_SETS:
            hits = tpl_usage.get(camel, {}).get(tset, [])
            tpl_cols[tset] = str(len(hits)) if hits else ""
        js_hits = js_usage.get(camel, [])
        srv_hits = srv_usage.get(dot.rstrip("*"), []) if dot else []
        scopes = set()
        for tset, hits in tpl_usage.get(camel, {}).items():
            for (fp, _ln) in hits:
                scopes.add(page_guard_for_template(fp))
        scope_str = "; ".join(sorted(scopes)) if scopes else ("JS-only" if js_hits else "")
        all_rows.append({
            "camelKey": camel, "dotKey": dot, "in_c": in_c,
            "default": tpl_cols["default"], "bootstrap2": tpl_cols["bootstrap2"],
            "bootstrap5": tpl_cols["bootstrap5"], "twigged": tpl_cols["twigged"],
            "twigged5": tpl_cols["twigged5"],
            "js_hits": len(js_hits),
            "js_files": "; ".join(sorted({f for f, _ in js_hits})),
            "server_hits": len(srv_hits),
            "server_files": "; ".join(sorted({f for f, _, _ in srv_hits})),
            "page_scope": scope_str,
        })
    server_only = sorted(set(srv_usage) - set(camel2dot.values()))
    with open(OUT_CSV, "w", newline="", encoding="utf-8") as fh:
        w = csv.DictWriter(fh, fieldnames=["camelKey", "dotKey", "in_c", "default", "bootstrap2", "bootstrap5", "twigged", "twigged5", "js_hits", "js_files", "server_hits", "server_files", "page_scope"])
        w.writeheader()
        w.writerows(all_rows)
    total_lang = len(lang_keys)
    orphan_js = [r for r in all_rows if r["in_c"] == "N" and r["js_hits"] > 0]
    unused_c = [r for r in all_rows if r["in_c"] == "Y" and not r["default"] and not r["bootstrap2"] and not r["bootstrap5"] and not r["twigged"] and not r["twigged5"] and r["js_hits"] == 0]
    unmapped = [r for r in orphan_js if not r["dotKey"]]
    shelves_rows = [r for r in all_rows if "shel" in r["camelKey"].lower()]
    md = []
    md.append("# i18n inventory\n")
    md.append(f"Generated from `lang/Localization_en.json` ({total_lang} keys), `src/Output/JsonRenderer.php::getCompleteArray()` ({len(camel2dot)} camel keys), templates + JS + server `localize()`.")
    md.append("Full table: `tools/i18n-inventory.csv` (one row per client camelKey).\n")
    md.append("## 1. How to read it\n")
    md.append("- `camelKey` = `it.c.i18n.xxx` / `getI18n('xxx')`; `dotKey` = `Localization_*.json` key.")
    md.append("- `in_c=Y` means present in `getCompleteArray()` (available after initial `complete=1` load).")
    md.append("- `default/bootstrap2/bootstrap5/twigged/twigged5` = occurrence counts in `templates/<set>/**/*.html`.")
    md.append("- `js_files` covers `util.js`, `resources/**/*.js`, `templates/*/scripts/*.js`.")
    md.append("- `server_files` covers `src/Pages + src/Output (+Calibre/Model)` `localize()` — baked into JSON title/content.")
    md.append("- `page_scope`: page-exclusive template vs global chrome vs shared vs JS-only.\n")
    md.append("## 2. Orphans: used in JS but NOT in `c.i18n` (always English fallback today)\n")
    md.append("| camelKey | dotKey | js_files |")
    md.append("|---|---|---|")
    for r in orphan_js:
        md.append(f"| {md_cell(r['camelKey'], True)} | {md_cell(r['dotKey'] or '?', True)} | {md_cell(r['js_files'])} |")
    if not orphan_js:
        md.append("(none)")
    md.append("\n## 3. In `c.i18n` but unused in any template/JS\n")
    md.append("| camelKey | dotKey |")
    md.append("|---|---|")
    for r in unused_c:
        md.append(f"| {md_cell(r['camelKey'], True)} | {md_cell(r['dotKey'], True)} |")
    if not unused_c:
        md.append("(none)")
    md.append("\n## 4. Shelves-related client keys (page-specific candidates)\n")
    md.append("Dots marked `*` are heuristic guesses (camel -> dot exists in en.json but NOT in getCompleteArray).\n")
    md.append("| camelKey | dotKey | in_c | twigged | twigged5 | bootstrap2 | bootstrap5 | default | js_hits | page_scope |")
    md.append("|---|---|---|---|---|---|---|---|---|---|")
    for r in shelves_rows:
        md.append(f"| {md_cell(r['camelKey'], True)} | {md_cell(r['dotKey'] or '?', True)} | {md_cell(r['in_c'])} | {md_cell(r['twigged'] or 0)} | {md_cell(r['twigged5'] or 0)} | {md_cell(r['bootstrap2'] or 0)} | {md_cell(r['bootstrap5'] or 0)} | {md_cell(r['default'] or 0)} | {md_cell(r['js_hits'])} | {md_cell(r['page_scope'])} |")
    json_rows = [r for r in all_rows if r["camelKey"] in camel2dot]
    md.append("\n## 5. JsonRenderer `c.i18n` keys (reference)\n")
    md.append("| camelKey | dotKey | in_c | twigged | twigged5 | bootstrap2 | bootstrap5 | default | js_hits | page_scope |")
    md.append("|---|---|---|---|---|---|---|---|---|---|")
    for r in json_rows:
        md.append(f"| {md_cell(r['camelKey'], True)} | {md_cell(r['dotKey'] or '?', True)} | {md_cell(r['in_c'])} | {md_cell(r['twigged'] or 0)} | {md_cell(r['twigged5'] or 0)} | {md_cell(r['bootstrap2'] or 0)} | {md_cell(r['bootstrap5'] or 0)} | {md_cell(r['default'] or 0)} | {md_cell(r['js_hits'])} | {md_cell(r['page_scope'])} |")
    md.append("\n## 6. Server-only dot keys (selection)\n")
    md.append(f"{len(server_only)} `localize()` dots have no `c.i18n` alias (baked server-side). Examples:")
    for d in server_only[:40]:
        pages = sorted({h for _, _, h in srv_usage[d] if h})
        md.append(f"- `{d}` ({len(srv_usage[d])}x — pages: {', '.join(pages) or 'shared/renderer'})")
    md.append("\n## 7. Regenerate\n")
    md.append("```sh\npython3 tools/i18n-inventory.py\n```")
    # per-file detail: which term in which file (answers "which page(s) for each template set")
    with open(OUT_DETAIL, "w", newline="", encoding="utf-8") as fh:
        w = csv.DictWriter(fh, fieldnames=["camelKey", "dotKey", "in_c", "source", "template_set", "file", "line", "page_scope"])
        w.writeheader()
        for camel in sorted(all_camel):
            dot = camel2dot.get(camel, "") or guess_dot(camel, lang_keys)
            in_c = "Y" if camel in camel2dot else "N"
            for tset, hits in sorted(tpl_usage.get(camel, {}).items()):
                for (fp, ln) in hits:
                    w.writerow({"camelKey": camel, "dotKey": dot, "in_c": in_c, "source": "template-html", "template_set": tset, "file": fp, "line": ln, "page_scope": page_guard_for_template(fp)})
            for (fp, ln) in js_usage.get(camel, []):
                parts = fp.split(chr(47))
                if fp.startswith("templates/"):
                    tset = parts[1]
                else:
                    tset = "shared-js"
                w.writerow({"camelKey": camel, "dotKey": dot, "in_c": in_c, "source": "js", "template_set": tset, "file": fp, "line": ln, "page_scope": "JS"})
    OUT_MD.write_text("\n".join(md) + "\n", encoding="utf-8")
    print(f"wrote {OUT_CSV} ({len(all_rows)} rows), {OUT_DETAIL}, {OUT_MD}")
    print(f"orphan JS keys (in_c=N): {len(orphan_js)}, unused c keys: {len(unused_c)}, lang keys: {total_lang}")

if __name__ == "__main__":
    main()
