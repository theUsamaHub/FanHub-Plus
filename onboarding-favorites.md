# Onboarding Favorites Feature - Implementation Plan

## Overview
Implement a full-screen modal onboarding flow for new users that requires selecting favorite fandoms/categories (minimum 3, maximum 5) from all available categories. Users must complete this before accessing the platform. The selections will drive content personalization across the platform.

## Requirements Summary
- **Modal Type**: Full-screen modal (not a page redirect)
- **Category Selection**: Minimum 3, Maximum 5 favorites
- **Categories**: Display ALL categories from database
- **Existing Users**: Will see modal on next login if not completed
- **No API**: Server-side rendered only
- **Persistence**: Store in `user_favorite_categories` pivot table
- **Content Personalization**: Show favorite categories first across all content types

## Technical Stack
- Laravel 13 (PHP 8.5)
- Blade templates with Alpine.js
- Tailwind CSS for styling
- Alpine.js for modal interactions

## Implementation Phases

### Phase 1: Database & Model Preparation (30 min)
- [ ] Verify `user_favorite_categories` pivot table exists
- [ ] Verify `UserProfile::onboarding_completed_at` field exists
- [ ] Add `favoriteCategoriesCount` accessor to UserProfile
- [ ] Add `hasCompletedOnboarding()` method to User model

### Phase 2: Backend - Controller & Routes (1.5 hours)
- [ ] Create `OnboardingController` with `create()` and `store()` methods
- [ ] Add routes: `GET /onboarding` (show modal), `POST /onboarding` (save favorites)
- [ ] Update `RegisteredUserController@store` to redirect to onboarding
- [ ] Create `EnsureOnboardingCompleted` middleware
- [ ] Register middleware in `Kernel.php`
- [ ] Apply middleware to protected routes

### Phase 3: Frontend - Modal UI (2 hours)
- [ ] Create full-screen modal component with Alpine.js
- [ ] Display ALL categories from database with icons/images
- [ ] Implement min 3 / max 5 selection validation
- [ ] Add selection counter and visual feedback
- [ ] Add "Complete Setup" button (disabled until min 3 selected)
- [ ] Add loading states and error handling
- [ ] Ensure modal cannot be dismissed (no backdrop click, no ESC key)

### Phase 4: Content Personalization (2 hours)
- [ ] Update `HomepageService::sections()` to prioritize favorite categories
- [ ] Add `scopeForUser($user)` scopes to Content, Event, Character, MerchandiseItem models
- [ ] Update queries in HomeController, EventController, CharacterController, MerchandiseController

### Phase 5: Middleware & Route Protection (1 hour)
- [ ] Create `EnsureOnboardingCompleted` middleware
- [ ] Register in `app/Http/Kernel.php`
- [ ] Apply to web routes (exclude onboarding routes, auth routes, logout)

### Phase 6: Update Registration Flow (30 min)
- [ ] Modify `RegisteredUserController@store` to redirect to onboarding
- [ ] Update middleware to exclude onboarding routes
- [ ] Test existing users see modal on next login

### Phase 6: Testing & Polish (1 hour)
- [ ] Test new user registration flow
- [ ] Test existing user login sees modal
- [ ] Verify min 3 / max 5 validation works
- [ ] Test content prioritization works
- [ ] Test middleware blocks access correctly
- [ ] Test modal cannot be dismissed

## Database Changes Required
None - existing tables sufficient:
- `user_profiles.onboarding_completed_at` (timestamp, nullable)
- `user_favorite_categories` pivot table (user_id, category_id, created_at)

## New Files to Create
1. `app/Http/Controllers/Auth/OnboardingController.php`
2. `app/Http/Middleware/EnsureOnboardingCompleted.php`
3. `resources/views/auth/onboarding.blade.php` (modal partial)
3. `resources/views/partials/onboarding-modal.blade.php` (if separate)

### Modified Files
1. `app/Http/Controllers/Auth/RegisteredUserController.php` - redirect to onboarding
2. `app/Http/Middleware/EnsureOnboardingCompleted.php` (new)
3. `app/Http/Kernel.php` - register middleware
4. `routes/web.php` - add onboarding routes
5. `app/Http/Controllers/Auth/RegisteredUserController.php` - redirect after registration
6. `app/Http/Middleware/RoleMiddleware.php` - exclude onboarding routes
7. `app/Http/Controllers/Auth/OnboardingController.php` (new)
8. `app/Models/User.php` - add helper methods
8. `resources/views/auth/onboarding.blade.php` (new modal)
9. `resources/views/partials/onboarding-modal.blade.php` (new)
10. `app/Services/HomepageService.php` - personalize content
11. `app/Http/Controllers/HomeController.php` - pass user preferences
12. `app/Http/Controllers/User/DashboardController.php` - personalize
13. `app/Models/User.php` - add helper methods
14. `app/Models/Category.php` - add scope for user favorites

## Validation Rules
- Minimum 3 categories selected
- Maximum 5 categories selected
- Categories must exist in database
- User must be authenticated

## Modal Behavior Requirements
- Full-screen modal (not centered small modal)
- Cannot dismiss by clicking backdrop
- Cannot dismiss with ESC key
- Cannot close without selecting min 3 categories
- Show all categories with icons/images
- Visual feedback on selection (highlight, checkmark)
- Counter showing "X of 5 selected"
- Submit button disabled until min 3 selected
- Success toast on completion, then redirect to dashboard

## Content Personalization Logic
For each content type (Content, Events, Characters, Merchandise):
1. Get user's favorite category IDs
2. Query: favorite categories first (ORDER BY CASE WHEN category_id IN (?) THEN 0 ELSE 1 END)
3. Then general content
4. Limit results appropriately per section

## Branch
`feature/onboarding-favorites` (created from main)

---

## Implementation Order
1. Create branch `feature/onboarding-favorites`
2. Create plan document (this file)
3. Database/Model verification
4. OnboardingController + routes
5. Onboarding modal view (Alpine.js)
6. Middleware + kernel registration
7. Update registration flow
7. HomepageService personalization
8. Model scopes for personalization
9. Controller updates for personalization
10. Testing

---

## Risk Mitigation
- Modal cannot be dismissed - ensure no accessibility issues (add focus trap)
- Existing users: middleware checks `onboarding_completed_at` null
- Performance: Cache user favorite categories in session
- Fallback: If no categories in DB, skip onboarding

## Acceptance Criteria
1. New user registers → sees full-screen modal immediately after email verification
2. Must select 3-5 categories to proceed
3. Modal cannot be closed by backdrop click or ESC
7. After submit → `onboarding_completed_at` set → redirect to dashboard
8. Home page shows favorite categories first
9. Events/Characters/Content pages prioritize favorites
10. Existing users with null `onboarding_completed_at` see modal on login