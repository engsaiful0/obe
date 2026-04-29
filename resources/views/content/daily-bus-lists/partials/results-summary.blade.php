<strong>Total Records:</strong> {{ $dailyBusLists->total() }} | 
<strong>Showing:</strong> {{ $dailyBusLists->firstItem() ?? 0 }} to {{ $dailyBusLists->lastItem() ?? 0 }} of {{ $dailyBusLists->total() }}
