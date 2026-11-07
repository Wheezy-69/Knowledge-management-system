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
export interface ProblemSummary {
    preview?: { field: string; text: string };
    id: number;
    section_id: number;
    title: string;
    description: string | null;
    programming_language: string;
    tags: string[] | null;
    attempts_count?: number;
    section?: Pick<Section, 'id' | 'vault_id' | 'name'> & {
        vault?: Pick<Vault, 'id' | 'name'>;
    };
}
export interface Problem extends ProblemSummary {
    corrected_solution: string | null;
    explanation: string | null;
    attempts?: Attempt[];
    blocks?: ContentBlock[];
}
export interface ContentBlock {
    id: number;
    problem_id: number;
    type: 'text' | 'code';
    content: string;
    language: string | null;
    position: number;
    source: string | null;
}
export interface RelatedProblem {
    id: number;
    title: string;
    section_id: number;
    section: { id: number; vault_id: number; name: string };
}
export interface SearchFilters {
    search: string;
    vault_id: number | string;
    section_id: number | string;
    language: string;
    tag_ids: number[];
}
export interface FilterOptions {
    vaults: { id: number; name: string }[];
    sections: { id: number; vault_id: number; name: string }[];
    languages: string[];
    tags: { id: number; name: string }[];
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
