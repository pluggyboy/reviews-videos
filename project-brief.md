# Social Video Generator for Google Reviews
## Project Brief

### Project Overview
The "Review Showcase" WordPress plugin transforms Google My Business reviews into engaging social videos. Business owners can select their favorite reviews, pair them with professional stock video backgrounds, and create ready-to-share videos for social media promotion. The solution will be built as a WordPress plugin, initially for local development on XAMPP.

### Target Users
- Small to medium-sized business owners
- Marketing staff responsible for social media content
- Service businesses that rely heavily on reviews (restaurants, hotels, contractors, etc.)

### Core Features

#### 1. Google Integration
- OAuth connection to Google My Business account
- Automatic fetching and syncing of Google reviews
- Storage of reviews in WordPress database

#### 2. Review Selection & Management
- Dashboard to browse, filter, and select reviews
- Rating filter (e.g., show only 4-5 star reviews)
- Ability to edit displayed review text (trimming, fixing typos)
- Option to save favorite reviews

#### 3. Video Creation
- Library of stock background videos categorized by industry/mood
- Text overlay customization (font, color, size, position)
- Business logo placement option
- Basic animations for review text (fade in, slide, etc.)
- Preview functionality before final rendering

#### 4. Video Processing
- Backend processing using FFmpeg
- Multiple aspect ratio options (16:9, 9:16 for stories, 1:1 for feed)
- Quality settings for different use cases
- Progress indicator during processing

#### 5. Export & Sharing
- Direct download of finished videos
- One-click sharing to social platforms (future enhancement)
- Video management library for reusing created content

### User Journey

#### 1. Setup & Connection
1. User installs the WordPress plugin
2. User navigates to "Review Showcase" in the WordPress admin menu
3. First-time setup wizard guides connection to Google My Business
4. User authorizes the app via OAuth
5. Initial sync pulls in existing reviews

#### 2. Review Management
1. User views all synced reviews in a dashboard interface
2. User can filter reviews by star rating, date, or content
3. User selects a review they want to showcase
4. System offers option to edit display text if needed

#### 3. Video Creation
1. User is presented with video template options
2. User selects a background video from the library
3. User customizes text appearance (font, color, position)
4. User adds business logo if desired
5. User previews how the video will look with the review
6. User makes adjustments as needed

#### 4. Processing & Export
1. User clicks "Generate Video" button
2. System shows progress as FFmpeg processes the video
3. Upon completion, preview of final video is shown
4. User can download the video or create another

### UI Design Specifications

#### Dashboard
- Clean, modern WordPress admin interface
- Left sidebar with function categories: Reviews, Templates, Videos, Settings
- Main content area with card-based display of reviews
- Filter controls at the top (date range, star rating, search box)
- Responsive design that works on tablets for on-the-go use

#### Review Selection Screen
- Card layout showing review text, author name, rating, and date
- Hover effects to highlight selectable reviews
- Quick action buttons (Edit, Create Video, Favorite)
- Pagination or infinite scroll for businesses with many reviews

#### Video Editor
- Split-screen interface: preview on right, controls on left
- Background video selection with thumbnail previews
- Text styling controls with real-time preview
- Position adjustment with drag-and-drop interface
- Aspect ratio selector with visual indicators

#### Video Processing Screen
- Progress bar showing encoding status
- Estimated time remaining
- Preview of final output when complete
- Direct download button and "Create Another" option

### Technical Architecture

#### WordPress Integration
- Custom post types for storing reviews and generated videos
- WordPress settings API for configuration options
- Custom admin pages for the main interfaces
- WordPress cron for periodic review syncing

#### Google Integration
- Google API Client library for PHP
- Secure storage of OAuth credentials in WordPress
- Background processing for review fetching

#### Video Processing
- FFmpeg integration via PHP's exec() function
- Queue system for processing multiple videos
- Temporary storage management for video files
- Error handling and logging

#### Frontend Enhancements
- React.js for the video editor interface
- Canvas previews for real-time editing
- AJAX for asynchronous processing requests
- Responsive design principles throughout

### Development Phases

#### Phase 1: Core Functionality
- WordPress plugin structure setup
- Google My Business API integration
- Basic review management and display
- Simple video generation with fixed templates

#### Phase 2: Enhanced Video Editor
- Custom text positioning and styling
- Multiple background video options
- Preview functionality
- Basic animations

#### Phase 3: Optimization & Polish
- Performance improvements for video processing
- Enhanced UI/UX refinements
- Additional customization options
- User feedback implementation

### Local Development Requirements
- XAMPP environment with PHP 7.4+
- WordPress installation
- FFmpeg installed locally
- Sufficient disk space for video processing
- Google API project with proper credentials

### Future Enhancements (Post-MVP)
- Direct social media posting integration
- Additional video effects and transitions
- Audio background options
- Scheduled posting of review videos
- Analytics on video performance
- QR code overlay option for customer acquisition

### Success Metrics
- Number of videos generated
- Time saved vs. manual video creation
- Increase in social media engagement
- User retention and plugin usage frequency
