export interface Vault {
    id: number;
    name: string;
    description: string | null;
    cover_url: string | null;
    sections_count?: number;
}
export interface Section {
    id: number;
    vault_id: number;
    name: string;
    description: string | null;
    problems_count?: number;
    vault?: Vault;
}
export interface Attempt {
    id: number;
    problem_id: number;
    code: string;
    notes: string | null;
    position: number;
}
export interface Problem {
    id: number;
    section_id: number;
    title: string;
    description: string | null;
    programming_language: string;
    tags: string[] | null;
    corrected_solution: string | null;
    explanation: string | null;
    attempts_count?: number;
    attempts?: Attempt[];
    section?: Section;
}
export interface Paginated<T> {
    data: T[];
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}
export interface Crumb {
    label: string;
    href?: string;
}
