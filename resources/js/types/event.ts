export interface Event {
  id: string;
  organization_id: string;
  name: string;
  description?: string;
  start_date: string;
  end_date: string;
  timezone?: string;
  location: string;
  status: string;
  is_parent: boolean;
  parent_event_id?: string;
  created_at: string;
  updated_at: string;
}
