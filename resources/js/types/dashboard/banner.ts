export interface Banner {
  id: string;
  title: string;
  image: string;
  image_signed_url?: string;
  status: 'active' | 'inactive';
  sequence: number;
  event_id?: string;
  event?: {
    id: string;
    name: string;
  };
  created_at: string;
  updated_at: string;
}
