import assert from 'node:assert/strict';
import test from 'node:test';
import { highlight } from '../../resources/js/lib/highlight.ts';

await test('unknown languages fall back to escaped plain text', () => {
    assert.equal(
        highlight('<script>alert(1)</script>', 'unknown-language'),
        '&lt;script&gt;alert(1)&lt;/script&gt;',
    );
});

await test('HTML-like source never becomes executable elements', () => {
    const html = highlight(
        '<img src=x onerror=alert(1)><script>alert(2)</script>',
        'html',
    );
    assert.doesNotMatch(html, /<(?:script|img)\b/i);
    assert.match(html, /&lt;/);
});

await test('registered languages and aliases produce highlighting', () => {
    for (const language of [
        'PHP',
        'js',
        'typescript',
        'python',
        'sql',
        'java',
        'rust',
    ]) {
        const html = highlight('function example() { return 42; }', language);
        assert.match(html, /hljs-/);
    }
});
