<?php

return [
	'list' => [
		[
			'name' => 'events.manage',
			'label' => 'Event Management',
			'children' => [
				['name' => 'events.create', 'label' => 'Create Events'],
				['name' => 'events.read', 'label' => 'View Events'],
				['name' => 'events.update', 'label' => 'Edit Events'],
				['name' => 'events.delete', 'label' => 'Delete Events'],
				['name' => 'events.publish', 'label' => 'Publish Events'],
			]
		],
		[
			'name' => 'tickets.manage',
			'label' => 'Ticket Management',
			'children' => [
				['name' => 'tickets.create', 'label' => 'Create Tickets'],
				['name' => 'tickets.read', 'label' => 'View Tickets'],
				['name' => 'tickets.update', 'label' => 'Edit Tickets'],
				['name' => 'tickets.delete', 'label' => 'Delete Tickets'],
			]
		],
		[
			'name' => 'orders.manage',
			'label' => 'Order Management',
			'children' => [
				['name' => 'orders.read', 'label' => 'View Orders'],
				['name' => 'orders.update', 'label' => 'Process Orders'],
				['name' => 'orders.export', 'label' => 'Export Orders'],
			]
		],
		[
			'name' => 'organizations.manage', // This is mostly for Super Admin
			'label' => 'Organization Management',
			'children' => [
				['name' => 'organizations.view_any', 'label' => 'View All Organizations'],
				['name' => 'organizations.create', 'label' => 'Create Organizations'],
				['name' => 'organizations.read', 'label' => 'View Organization Details'],
				['name' => 'organizations.update', 'label' => 'Edit Organizations'],
				['name' => 'organizations.delete', 'label' => 'Delete Organizations'],
				['name' => 'organizations.users.manage', 'label' => 'Manage Organization Users'],
				['name' => 'organizations.users.create', 'label' => 'Create Organization Users'],
				['name' => 'organizations.users.update', 'label' => 'Edit Organization Users'],
				['name' => 'organizations.users.delete', 'label' => 'Delete Organization Users'],
			]
		],
		[
			'name' => 'users.manage',
			'label' => 'User Management',
			'children' => [
				['name' => 'users.create', 'label' => 'Create Users'],
				['name' => 'users.read', 'label' => 'View Users'],
				['name' => 'users.update', 'label' => 'Edit Users'],
				['name' => 'users.delete', 'label' => 'Delete Users'],
			]
		],
		[
			'name' => 'roles.manage',
			'label' => 'Role & Permission Management',
			'children' => [
				['name' => 'roles.create', 'label' => 'Create Roles'],
				['name' => 'roles.read', 'label' => 'View Roles'],
				['name' => 'roles.update', 'label' => 'Edit Roles'],
				['name' => 'roles.delete', 'label' => 'Delete Roles'],
			]
		],
		[
			'name' => 'activity_logs.manage',
			'label' => 'Activity Logs',
			'children' => [
				['name' => 'activity_logs.read', 'label' => 'View Logs'],
				['name' => 'activity_logs.export', 'label' => 'Export Logs'],
			]
		],
		[
			'name' => 'reports.manage',
			'label' => 'Reports & Analytics',
			'children' => [
				['name' => 'reports.read', 'label' => 'View Reports'],
				['name' => 'reports.export', 'label' => 'Export Reports'],
			]
		],
		[
			'name' => 'settings.manage',
			'label' => 'Settings',
			'children' => [
				['name' => 'settings.read', 'label' => 'View Settings'],
				['name' => 'settings.update', 'label' => 'Edit Settings'],
			]
		],
	]
];
