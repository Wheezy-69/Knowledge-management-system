export async function copyText(
    text: string,
    clipboard: Pick<Clipboard, 'writeText'> | undefined = typeof navigator ===
    'undefined'
        ? undefined
        : navigator.clipboard,
): Promise<boolean> {
    if (!clipboard) return false;
    try {
        await clipboard.writeText(text);
        return true;
    } catch {
        return false;
    }
}
