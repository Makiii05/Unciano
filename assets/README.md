# assets/

Static frontend assets served directly by the web server.

## Structure

```
assets/
├── css/        # Custom CSS (Tailwind is compiled separately in src/)
├── js/         # Client-side JavaScript (reusable modules, Fetch helpers)
└── images/     # Logo, icons, static images
```

## Notes

- Tailwind CSS is compiled from `src/input.css` to `public/css/app.css`
- Put custom non-Tailwind CSS here
