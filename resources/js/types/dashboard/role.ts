export interface Role {
  id: number;
  name: string;
  guard_name: string;
  label?: string;
  parent_id?: number | null;
  users_count?: number;
  users?: { id: number }[]; // For pre-filling selection
  created_at?: string;
  updated_at?: string;
}

export interface CandidateUser {
  id: number;
  name: string;
  email: string;
}
