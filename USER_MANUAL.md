# UiTM STEP User Manual
## Student Talent Empowerment Platform

Welcome to the UiTM STEP User Manual. This guide shows you how to use the platform's features, manage your account, order services, and work with other students.

---

## Table of Contents
1. Introduction and platform overview
2. Getting started (Onboarding)
3. User account and profile management
4. The marketplace and gig ecosystem
5. Communication and orders
6. Platform guides and support pages
7. Administrative oversight (Admin panel)
8. Screenshot and highlighting guide

---

## 1. Introduction and platform overview

UiTM STEP (Student Talent Empowerment Platform) is a secure campus marketplace made for students of Universiti Teknologi MARA (UiTM). The platform lets students offer their skills and hire fellow classmates for projects.

### Key benefits
* Peer to peer hiring: Hire student freelancers directly or earn money by offering your own services.
* Campus filters: Search for helpers on your specific campus to make local meetings and handovers easy.
* Safe workspaces: Message other students, make secure payments, and resolve issues safely within the platform.

---

## 2. Getting started (Onboarding)

The onboarding flow makes sure only verified students can join the network.

```
[Sign up] ──> [Email verification code] ──> [Campus selection] ──> [Start using the platform]
```

### Sign up (`register.php`)
* Student email check: You must sign up with a student email address (usually ending with `@student.uitm.edu.my`). The system does not accept other emails.
* Automatic student ID: The system reads your 10 digit student ID directly from your email address. For example, `2024123456@student.uitm.edu.my` registers you under the student ID `2024123456`.
* Account security: Passwords must be at least 6 characters. The system creates a 6 digit code to confirm your email.
* Note on testing: In testing mode, your verification code is displayed in a pop-up window so you can test registration easily.

### Email verification (`verify-email.php`)
* After submitting the sign up form, you will go to the email verification screen.
* Enter the 6 digit code sent to your student email. Once you enter the correct code, your account is activated and you can log in.

### Complete registration (`complete-registration.php`)
* If you log in using an external account (like Google) and did not choose a campus, the platform will ask you to pick one before you can browse.
* Choose your campus from the official UiTM branches listed. The form uses security tokens to protect your submission.

---

## 3. User account and profile management

Once registered, you can personalize your profile and track your orders.

### Profile customization (`profile.php`)
* Profile picture: Upload a photo of yourself. The photo must be in JPG, PNG, GIF, or WEBP format and under 5MB.
* Password changes: Update your password by entering your current password and choosing a new one that is at least 6 characters.
* Public preview: You can preview how your public profile page looks to other students by clicking the preview option or viewing your profile link.

### Central command hub (`dashboard.php`)
You can switch your dashboard between two views depending on what you want to do:

#### A. Buying view
* Statistics: See your total spending, active hires, and completed jobs.
* Order history: Search through your orders by gig title or seller name. Filter them by status (pending, paid, delivered, complete, or cancelled).

#### B. Selling view
* Sales statistics: Track your total earnings with visual charts.
* Gig management: Manage your listings, view incoming orders, create new service offerings, and preview your seller page.

---

## 4. The marketplace and gig ecosystem

The marketplace lists all available services, and the gig ecosystem handles creation and booking.

### Browsing the marketplace (`marketplace.php`)
* Clean layouts: Gigs are listed in a grid of 12 items per page.
* Campus filters: Look for services on your local campus or search all campuses. If your local campus has no listings, the page will automatically show all campuses instead of leaving the screen empty.
* Tag and search tools: Search by typing keywords or filtering by category tags. View seller ratings and reviews.

### Creating and editing gigs (`/gigs/create.php`, `/gigs/edit.php`)
* Gig details: Provide a title, choose a category, write a description, set your price, add search tags, and paste an optional YouTube video link for your trailer.
* Cover image: Upload a cover photo that is under 5MB.
* Data security: Gigs are saved using database transaction blocks. This ensures your tags and description are saved together without losing data.

### Gig details and ordering (`/gigs/details.php`)
* Shows the service details, pricing, reviews, and a media viewer for images and YouTube videos.
* Ordering: Click "Buy Now" to order a service. The platform checks that you are not ordering your own gig, verifies that you do not already have an active order for this service, and takes you to the checkout screen.

---

## 5. Communication and orders

### Workspace chat system (`chat.php`, `/api/send_message.php`, `/api/fetch_messages.php`)
Good communication helps projects succeed. STEP has a built-in chat workspace:
* Contacts list: See all students you have active conversations with.
* Connection status: View online indicators next to user names.
* Safety filter banner: To protect students from scams, the chat monitors messages for external contacts (like WhatsApp, Telegram, or Discord). If found, a safety banner warns you to keep all chat and payments within STEP.
* Fast loading: The chat page is optimized to load message history instantly without slowing down your active messages.

### Order progression and secure checkout
Every order moves through a clear set of steps to protect your money:

```
[Pending checkout] ──> [Paid / Escrow holding] ──> [Delivered] ──> [Complete & Reviewed]
```

1. Secure checkout (`payment-gateway.php`):
   * Pay using Bank Cards, Online Banking (FPX, including a test bank option), or E-Wallets (like Touch 'n Go).
   * The checkout system generates a transaction receipt number starting with `STEP-TXN-`, marks the order as paid, and holds the funds safely.
2. Delivery: The freelancer uploads the completed files or links. The order status updates to delivered.
3. Completion: The buyer accepts the work. The status changes to complete, and the buyer can leave a star rating and a review.

---

## 6. Platform guides and support pages

### Knowledge guides (`/guides/`)
Read articles written to help you succeed:
* Portfolio building (`guides/portfolio.php`): How to create a professional freelance portfolio while studying at university.
* Pricing tips (`guides/pricing.php`): How to set rates and price your first freelance projects.
* Time management (`guides/time.php`): Tips on balancing your schoolwork, final exams, and freelancing.

### Support and feedback
* FAQ (`faq.php`): Answers about payments, deliveries, security, and campus filters.
* How it works (`how-it-works.php`): Simple steps for buyers and sellers.
* User feedback (`feedback.php`): Submit features suggestions or bug reports to the admin team.

---

## 7. Administrative oversight (Admin panel)

Administrators have access to management tools under the `/admin/` folder to keep the platform safe.

### Admin dashboard (`admin/index.php`)
* Dispute review: Admins can inspect order details and check payment receipts.
* User management: Search and filter the user list. Admins can update roles (student, admin, banned).
* Infraction center: Review reports sent by students. Report reasons include:
  * Scam / Fraud
  * Fake Payment Proof
  * Did Not Deliver Work
  * Harassment / Threats
  * Inappropriate Content
  * Other
  * Admins can mark reports as reviewed, dismiss them, or ban the reported user.

### Ban appeals (`admin/appeal_action.php` & `banned.php`)
* Account suspension: Suspended users are restricted to the `banned.php` screen. Their active gigs are hidden from the marketplace.
* Submitting appeals: Suspended users can send a one-time appeal message explaining their situation.
* Admin decisions: Admins review the appeal in their panel:
  * Approved: Reinstates the user to the student role, restores their listings, and adds a note.
  * Rejected: Permanently bans the account.

---

## 8. Screenshot and highlighting guide

Use this guide to compile an image based version of the manual. It lists exactly which pages to capture and which buttons, input boxes, or sections to highlight with visual markers (like red boxes or arrows).

### A. Onboarding and registration flow

#### Sign up page (`register.php`)
* Student email input: Highlight where users enter their university email.
* Student ID field: Highlight the read only student ID box that automatically reads their ID from their email.
* Campus dropdown selection: Highlight where students select their branch.
* Register button: Highlight the main submit button.

#### Email verification page (`verify-email.php`)
* Verification code input: Highlight the boxes where users type their 6 digit activation code.
* Verify now button: Highlight the button that activates the account.

#### Complete registration page (`complete-registration.php`)
* Campus dropdown: Highlight the dropdown where students select their branch.
* Complete registration button: Highlight the submit button.

### B. Profiles and settings

#### Profile settings page (`profile.php`)
* Avatar upload area: Highlight the profile picture uploader (noting the 5MB size limit).
* Name, bio, and campus fields: Highlight these basic details input boxes.
* Save profile button: Highlight the button that saves profile updates.
* Live preview button: Highlight the link that lets sellers see how their public page looks to others.

### C. Hires and earnings (Dashboards)

#### Buying view dashboard (`dashboard.php?mode=buying`)
* Dashboard selector: Highlight the active "Buying" tab.
* Overview statistics cards: Highlight the cards for total spending, active hires, and completed jobs.
* Search purchases bar: Highlight the search input box.
* Order status filter: Highlight the status dropdown filter.

#### Selling view dashboard (`dashboard.php?mode=selling`)
* Dashboard selector: Highlight the active "Selling" tab.
* Earnings chart: Highlight the visual sales graph.
* Create new gig button: Highlight the green button used to create a new service.
* Active orders table: Highlight the list showing incoming client jobs.

### D. Gigs and marketplace

#### Marketplace page (`marketplace.php`)
* Campus filter: Highlight where users filter for local campus helpers.
* Search and category tags: Highlight the main search inputs.
* Gig listing card: Highlight a single card showing seller details, campus name, price, and reviews.

#### Gig creation form (`/gigs/create.php`)
* Price input: Highlight the RM price input field.
* Gig image upload field: Highlight the cover picture upload area (under 5MB).
* YouTube URL field: Highlight the optional video link box.
* Publish button: Highlight the save button.

#### Gig details view (`/gigs/details.php`)
* Media player: Highlight the photo and video slideshow area.
* Seller card: Highlight the box showing the freelancer profile details and chat link.
* Buy now button: Highlight the booking button on the right sidebar.

### E. Checkout and messaging

#### Checkout portal (`payment-gateway.php`)
* Order summary: Highlight the box listing the gig title and final price.
* Payment options: Highlight the selection for cards, online banking, and e-wallets.
* Pay now button: Highlight the checkout button.

#### Workspace chat (`chat.php`)
* Contacts sidebar: Highlight the list showing ongoing chat threads.
* Safety banner: Highlight the yellow warning banner that appears if external links or phone numbers are shared.
* Quick action buttons: Highlight the "Deliver Order" button at the top of the chat thread.

### F. Administration and appeals

#### Admin dashboard (`/admin/index.php`)
* Overview stats: Highlight the badges showing total order numbers.
* Reported violations: Highlight the table listing user reports.
* User account rows: Highlight where admins can ban users or change roles.

#### Suspension notice (`banned.php`)
* Suspended warning text: Highlight the notification message.
* Appeal input box: Highlight the text area where suspended users type their appeal.
* Send appeal button: Highlight the submission button.
