<?php

return [
	'menu' => [
		[
			'group' => 'Overview',
			'items' => [
				[
					'title' => 'Dashboard',
					'url' => 'dashboard',
					'icon' => 'PieChart', // Mapped to LayoutGrid in AppSidebar
					// No permission required
				],
				[
					'title' => 'Analytics',
					'url' => 'analytics.index',
					'icon' => 'ChartArea',
					'permission' => 'reports.read',
				],
			],
		],
		[
			'group' => 'User Management',
			'items' => [
				[
					'title' => 'Users', // Global user list
					'url' => 'user.index',
					'icon' => 'Users',
					'permission' => 'users.read',
				],
				[
					'title' => 'Roles',
					'url' => 'role.index',
					'icon' => 'Shield',
					'permission' => 'roles.read',
				],
			],
		],
		[
			'group' => 'Organization Management',
			'items' => [
				[
					'title' => 'Organizations', // CRUD for Organizations
					'url' => 'organization.index',
					'icon' => 'Building2',
					'permission' => 'organizations.view_any',
				],
				[
					'title' => 'User Assignment', // Assign users to specific orgs
					'url' => 'organization.user.index',
					'icon' => 'UserPlus',
					'permission' => 'organizations.users.manage',
				],
			],
		],
		[
			'group' => 'Event Management',
			'items' => [
				[
					'title' => 'Events',
					'url' => 'event.index',
					'icon' => 'BookOpen',
					'permission' => 'events.read',
				],
				[
					'title' => 'Ticket Types',
					'url' => 'ticket_type.index',
					'icon' => 'Ticket',
					'permission' => 'tickets.read',
				],
				[
					'title' => 'Orders',
					'url' => 'order.index',
					'icon' => 'ShoppingBag',
					'permission' => 'orders.read',
				],
			],
		],
		[
			'group' => 'Content & Settings',
			'items' => [
				[
					'title' => 'Banners',
					'url' => 'banner.index',
					'icon' => 'Image',
					'permission' => 'settings.update',
				],
				[
					'title' => 'Platform Fee',
					'url' => 'platform_fee.index',
					'icon' => 'Settings',
					'permission' => 'settings.update',
				],
				[
					'title' => 'Activity Logs',
					'url' => 'activity-logs.index',
					'icon' => 'Activity',
					'permission' => 'activity_logs.read',
				]
			],
		],
	],
];
