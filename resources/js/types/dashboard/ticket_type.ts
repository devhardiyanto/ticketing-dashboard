export interface TicketType {
	id: string,
	event_id: string,
	name: string,
	description: string,
	price: number,
	quantity: number,
	sale_start_date: string,
	sale_end_date: string,
	status: string,
	created_at: string,
	updated_at: string,
}
