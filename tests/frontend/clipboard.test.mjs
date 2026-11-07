import assert from 'node:assert/strict';
import test from 'node:test';
import { copyText } from '../../resources/js/lib/clipboard.ts';

await test('copy sends exact original whitespace and Unicode to the clipboard', async () => {
    const code = '    日本語();\r\n\n';
    let copied;
    assert.equal(
        await copyText(code, {
            async writeText(value) {
                copied = value;
            },
        }),
        true,
    );
    assert.equal(copied, code);
});
await test('clipboard rejection returns failure rather than claiming success', async () => {
    assert.equal(
        await copyText('code', {
            async writeText() {
                throw new Error('denied');
            },
        }),
        false,
    );
});
