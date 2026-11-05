import hljs from 'highlight.js/lib/core';
import bash from 'highlight.js/lib/languages/bash';
import css from 'highlight.js/lib/languages/css';
import javascript from 'highlight.js/lib/languages/javascript';
import json from 'highlight.js/lib/languages/json';
import php from 'highlight.js/lib/languages/php';
import plaintext from 'highlight.js/lib/languages/plaintext';
import python from 'highlight.js/lib/languages/python';
import sql from 'highlight.js/lib/languages/sql';
import typescript from 'highlight.js/lib/languages/typescript';
import xml from 'highlight.js/lib/languages/xml';
import java from 'highlight.js/lib/languages/java';
import csharp from 'highlight.js/lib/languages/csharp';
import cpp from 'highlight.js/lib/languages/cpp';
import go from 'highlight.js/lib/languages/go';
import rust from 'highlight.js/lib/languages/rust';
import ruby from 'highlight.js/lib/languages/ruby';

for (const [name, grammar] of Object.entries({
    bash,
    css,
    javascript,
    json,
    php,
    plaintext,
    python,
    sql,
    typescript,
    xml,
    java,
    csharp,
    cpp,
    go,
    rust,
    ruby,
}))
    hljs.registerLanguage(name, grammar);

export function highlight(code: string, language: string): string {
    const requested = language.trim().toLowerCase();
    return hljs.highlight(code, {
        language: hljs.getLanguage(requested) ? requested : 'plaintext',
        ignoreIllegals: true,
    }).value;
}
