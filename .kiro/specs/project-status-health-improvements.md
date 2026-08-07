# Project Status and Health Improvements Spec

## Overview
This spec addresses issues with project status updates, health status management, and UI improvements for the project management system.

## Current Issues
1. ❌ Status updates are incorrectly changing project colors (only health should affect colors)
2. ❌ Status update responses are slow (3-5 second delay before showing success notification)
3. ❌ Projects jump positions when status is updated (should stay in place)
4. ❌ Missing Health update functionality in bulk operations
5. ❌ Project detail layout needs adjustment (milestone column positioning)

## Requirements

### 1. Separate Status and Health Concerns
**Status:** Approved  
**Priority:** High

- Status (`tracking_status`) should be: `following`, `not_following`, `completed`
- Health should be: `green`, `yellow`, `red`
- **Status changes should NOT affect project card color**
- **Only Health status should determine card background color**
- Badge text should show status, but color styling comes from health

### 2. Fix Status Update Performance
**Status:** Approved  
**Priority:** High

- Identify why status updates take 3-5 seconds
- Optimize database queries
- Consider optimistic UI updates
- Add proper loading states

### 3. Prevent Position Changes on Update
**Status:** Approved  
**Priority:** High

- Status/health updates should not trigger `last_activity_at` changes
- Projects should maintain their sort position
- Only meaningful updates (title, description, assignments) should change activity timestamp

### 4. Add Health Update to Bulk Operations
**Status:** Approved  
**Priority:** Medium

- Add "Update Health" button to bulk operations toolbar
- Should work similar to "Update Status"
- Options: Tốt (Xanh), Bình thường (Vàng), Có vấn đề (Đỏ)

### 5. Project Detail Layout Improvements
**Status:** Approved  
**Priority:** Medium

**Current Layout:**
```
[HOẠT ĐỘNG - Full Height]  |  [CỘT MỐC - Half] + [CÔNG VIỆC - Half]
```

**New Layout:**
```
[HOẠT ĐỘNG - Full Height]  |  [CỘT MỐC - Half (387px)] 
                           |  [CÔNG VIỆC - Half (387px)]
```

**Requirements:**
- Move "Cột mốc" (Milestones) to right column, above "Công việc"
- Both Cột mốc and Công việc blocks should have equal height (387px each)
- Hoạt động column should span both heights (800px total)
- Change "Mốc thời gian" label to "Cột mốc"

### 6. Status Badge Color Unification
**Status:** Approved  
**Priority:** Low

- All status badges should use consistent text color
- Remove status-dependent color variations
- Keep simple, unified styling

## Design Specifications

### Color Mapping (Health Only)
```javascript
// Card Background Colors (Based on Health)
{
  green: 'bg-emerald-50/40 border-emerald-200',
  yellow: 'bg-amber-50/40 border-amber-200',
  red: 'bg-rose-50/40 border-rose-200'
}

// Status Badge (Text Only, No Color Coding)
{
  following: { text: 'Đang theo', class: 'bg-gray-100 text-gray-700' },
  not_following: { text: 'Không theo', class: 'bg-gray-100 text-gray-700' },
  completed: { text: 'Hoàn thành', class: 'bg-gray-100 text-gray-700' }
}
```

## Technical Implementation

### Backend Changes (Laravel)

#### 1. Fix updateStatus/updateHealth Methods
```php
// ProjectController.php
public function updateStatus(Request $request, $id)
{
    // Should NOT update last_activity_at
    // Should NOT sync with health
    // Should be instant (optimize queries)
}

public function updateHealth(Request $request, $id)
{
    // Should NOT update last_activity_at
    // Should NOT sync with tracking_status
    // Should be instant (optimize queries)
}
```

#### 2. Add Bulk Health Update Endpoint
```php
Route::post('/projects/bulk-health', [ProjectController::class, 'bulkUpdateHealth']);
```

### Frontend Changes (Vue.js)

#### 1. Fix getProjectStatusStyle Method
```javascript
// Should return styles based on HEALTH, not status
getProjectStatusStyle(project) {
  const healthColors = {
    green: { cardBg: 'bg-emerald-50/40', borderClass: 'border-emerald-200' },
    yellow: { cardBg: 'bg-amber-50/40', borderClass: 'border-amber-200' },
    red: { cardBg: 'bg-rose-50/40', borderClass: 'border-rose-200' }
  };
  
  const statusLabels = {
    following: 'Đang theo',
    not_following: 'Không theo',
    completed: 'Hoàn thành'
  };
  
  return {
    ...healthColors[project.health],
    badgeText: statusLabels[project.tracking_status],
    badgeClass: 'bg-gray-100 text-gray-700' // Unified
  };
}
```

#### 2. Add Optimistic UI Updates
```javascript
async bulkUpdateStatus(status) {
  // Update UI immediately
  this.selectedProjectIds.forEach(id => {
    const project = this.projects.find(p => p.id === id);
    if (project) {
      project.tracking_status = status; // Only status changes
      // DO NOT change color/health
    }
  });
  
  // Then send request
  await api.post('/projects/bulk-status', { ... });
  
  // Show success notification
}
```

#### 3. ProjectDetailPage Layout
```vue
<!-- New 2-column grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  <!-- Left: HOẠT ĐỘNG (800px) -->
  <div class="h-[800px] ...">...</div>
  
  <!-- Right: Two stacked blocks -->
  <div class="space-y-6">
    <!-- CỘT MỐC (387px) -->
    <div class="h-[387px] ...">...</div>
    
    <!-- CÔNG VIỆC (387px) -->
    <div class="h-[387px] ...">...</div>
  </div>
</div>
```

## Testing Checklist

- [ ] Status update does not change card color
- [ ] Health update changes card color correctly
- [ ] Status update completes in < 1 second
- [ ] Projects maintain position after status/health update
- [ ] Bulk health update works for multiple projects
- [ ] Project detail layout matches new specification
- [ ] All status badges have unified color styling

## Open Questions

1. Should we add loading indicators during bulk operations?
2. Do we need to throttle/debounce bulk update requests?
3. Should health dots also appear in grouped-by-customer view?

## Notes

- Health and Status are now completely independent fields
- Backend already has the separation logic in place (as per code review)
- Main issue is frontend incorrectly mixing health/status for styling
- Performance issue likely from unnecessary `last_activity_at` updates triggering re-sorts
