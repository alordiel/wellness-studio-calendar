# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress plugin called "Wellness Studio Calendar" that provides an events calendar system for yoga and wellness studios. The plugin allows studio owners to manage activities, instructors, locations, and events, while allowing users to book sessions through the frontend.

## Development Commands

### Frontend Development (packages/front-end/)
```bash
cd packages/front-end
npm run dev     # Start development server
npm run build   # Build for production
npm run preview # Preview production build
```

### Admin Development (packages/admin/)
```bash
cd packages/admin
npm run dev     # Start development server
npm run build   # Build for production
npm run preview # Preview production build
```

## Architecture

### Core Structure
- **Main plugin file**: `wellness-studio-calendar.php` - Entry point and activation hooks
- **PHP Classes**: Located in `includes/classes/` - Data models for Activity, Event, Instructor, Location, and Reservation
- **Database**: Custom MySQL tables with proper foreign key relationships
- **Admin Interface**: Vue.js 3 + Vuetify 3 admin dashboard in `packages/admin/`
- **Frontend**: Vue.js 3 + Tailwind CSS calendar view in `packages/front-end/`

### Database Tables
- `wsc_activity` - Practice classes/activities
- `wsc_instructors` - Instructor profiles
- `wsc_locations` - Studio locations and rooms
- `wsc_events` - Recurring weekly events
- `wsc_reservations` - User bookings
- `wsc_exceptions` - Event schedule exceptions

### Key PHP Classes
- **WSC_Event**: Main event management class with CRUD operations
- **WSC_Activity**: Activity/practice class management
- **WSC_Instructor**: Instructor profile management
- **WSC_Location**: Location and room management
- **WSC_Reservation**: Booking system management

### Frontend Architecture
- **Admin**: Vue 3 + Vuetify with component-based architecture
  - Store pattern for state management (Pinia-like stores)
  - Router for navigation between admin views
  - Modal components for CRUD operations
- **Frontend**: Vue 3 + Tailwind CSS for public calendar display
  - Booking modal component
  - Event listing and day sections

### Build System
- Uses Vite for both frontend and admin packages
- Separate build processes for admin and frontend
- Tailwind CSS for frontend styling
- Vuetify for admin UI components

## Key Development Notes

### Working with Events
- Events are recurring weekly events with specific days, times, and locations
- Event exceptions can modify or cancel specific dates
- Events link to activities, instructors, and locations through foreign keys

### Database Operations
- All database operations use WordPress $wpdb with prepared statements
- Foreign key constraints ensure data integrity
- Custom table structure with proper indexing

### WordPress Integration
- Uses WordPress hooks for activation/deactivation
- Integrates with WordPress REST API for frontend communication
- Uses WordPress nonce system for security
- Follows WordPress coding standards for PHP

### Frontend Development
- Admin uses CDN-hosted Vue.js and Vuetify
- Frontend uses CDN-hosted Vue.js with local Tailwind build
- AJAX communication with WordPress backend
- Component-based architecture for maintainability