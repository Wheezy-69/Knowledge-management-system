import assert from 'node:assert/strict';
import test from 'node:test';
import { compareLines } from '../../resources/js/lib/diff.ts';

await test('comparison preserves whitespace Unicode line endings and empty inputs', () => {
    for (const [a, b] of [
        ['  日本語\r\nend', '日本語\nend\n'],
        ['', 'new\n'],
        ['old', ''],
        ['', ''],
    ]) {
        const result = compareLines(a, b);
        assert.equal(result.limited, false);
        assert.equal(result.rows.map((r) => r.left ?? '').join(''), a);
        assert.equal(result.rows.map((r) => r.right ?? '').join(''), b);
    }
});
await test('comparison identifies additions and removals without rendering stored HTML', () => {
    const result = compareLines(
        '<script>old()</script>\nkeep\n',
        '<script>new()</script>\nkeep\n',
    );
    assert.deepEqual(
        result.rows.map((r) => r.kind),
        ['remove', 'add', 'equal'],
    );
    assert.equal(result.rows[0].left, '<script>old()</script>\n');
});
await test('large comparisons use the bounded fallback', () => {
    assert.equal(
        compareLines('x\n'.repeat(1000), 'y\n'.repeat(1000)).limited,
        true,
    );
    assert.equal(compareLines('x'.repeat(200001), '').limited, true);
});
