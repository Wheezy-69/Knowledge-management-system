export interface DiffRow {
    left: string | null;
    right: string | null;
    kind: 'equal' | 'remove' | 'add';
}
export interface Comparison {
    rows: DiffRow[];
    limited: boolean;
}
export function compareLines(left: string, right: string): Comparison {
    const split = (text: string): string[] =>
        text.match(/[^\n]*\n|[^\n]+$/g) ?? [];
    const a = split(left);
    const b = split(right);
    if (
        left.length + right.length > 200000 ||
        a.length * b.length > 250000 ||
        a.length + b.length > 4000
    )
        return { rows: [], limited: true };
    const width = b.length + 1;
    const lengths = new Uint16Array((a.length + 1) * width);
    for (let i = a.length - 1; i >= 0; i--)
        for (let j = b.length - 1; j >= 0; j--)
            lengths[i * width + j] =
                a[i] === b[j]
                    ? lengths[(i + 1) * width + j + 1] + 1
                    : Math.max(
                          lengths[(i + 1) * width + j],
                          lengths[i * width + j + 1],
                      );
    const rows: DiffRow[] = [];
    let i = 0;
    let j = 0;
    while (i < a.length || j < b.length) {
        if (i < a.length && j < b.length && a[i] === b[j]) {
            rows.push({ left: a[i++], right: b[j++], kind: 'equal' });
        } else if (
            i < a.length &&
            (j === b.length ||
                lengths[(i + 1) * width + j] >= lengths[i * width + j + 1])
        )
            rows.push({ left: a[i++], right: null, kind: 'remove' });
        else rows.push({ left: null, right: b[j++], kind: 'add' });
    }
    return { rows, limited: false };
}
