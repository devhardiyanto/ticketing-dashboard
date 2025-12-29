import { Organization } from "./organization";

export interface Event {
	id: string;
	organization_id: string;
	image_url?: string | null;
	name: string;
	slug: string;
	description?: string;
	start_date: string;
	end_date: string;
	timezone?: string;
	location: string;
	address?: string;
	status: 'draft' | 'published' | 'archived';
	currency: string;
	is_parent: boolean;
	parent_event_id?: string;
	created_at: string;
	updated_at: string;
	organization: Organization;
	child_events?: Event[];
}
