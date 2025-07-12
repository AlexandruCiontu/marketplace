# LaraStore Project Best Practices

This document outlines the best practices and standards for the LaraStore Laravel-React-Inertia project.

## Frontend Development

### UI Framework

- The website uses **Daisy UI** exclusively
- Only Daisy UI classes and components should be used for consistency
- Avoid using custom CSS classes unless absolutely necessary
- Follow Daisy UI's theming system for color consistency

### React Components

- Create reusable React components for commonly used UI elements
- Keep components small and focused on a single responsibility
- Use TypeScript for all React components
- Implement proper prop validation
- Do not add docblocks on classes and methods in React components

## Backend Development

### Models

- Follow Laravel's naming conventions for model names and relationships
- Keep models clean and focused on representing database structure
- Do not add docblocks on model classes and their methods

### Laravel Resources

- When returning data from Laravel to React through Inertia, **always create Resource classes**
- Place resource classes in `app/Http/Resources` directory
- Use resources to transform database models into a format suitable for the frontend
- Keep resource transformations consistent across the application
- Do not add docblocks on resource classes and their methods

### Controllers

- Use resource controllers where appropriate
- Keep controllers thin by moving business logic to services
- Use form requests for validation
- Do not add docblocks on classes and methods in PHP/Laravel code

## Data Flow

- Use Inertia.js for passing data from Laravel to React
- Follow the pattern: Model → Resource → Controller → Inertia → React Component
- Leverage Inertia's shared data for global state

## Security Practices

- Use Laravel's built-in CSRF protection
- Validate all user inputs using form requests
- Implement proper authorization using Laravel's policies

## Testing

- Write unit tests for PHP services and resource transformations
- Write feature tests for main application flows
- Implement React component testing for critical UI elements

This document will be updated as new standards are established for the project.
