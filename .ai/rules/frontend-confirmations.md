# Frontend Confirmation Dialog Standards

Target Globs: `resources/js/**`

## Rules

- **No Custom Delete Modals**: Do NOT build separate delete modal components for models.
- **Use `useConfirm()`**: Use the programmatic `useConfirm()` composable for single and bulk deletion confirmations across all frontend components.
