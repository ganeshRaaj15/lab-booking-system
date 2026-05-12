## SLAMS Deployment Model

`slams/` is the canonical SLAMS backend and website codebase.

- Deploy `slams/` to `https://slams.cloud`
- Serve the website, dashboards, and native API from this one backend
- Keep MySQL behind the backend on the same hosting account
- Expose the mobile API at `https://slams.cloud/api/native/*`

`slams-mobile/` is the mobile workspace.

- Build the installed mobile client from `slams-mobile/native-app`
- Point the app at `https://slams.cloud`
- Do not connect the mobile app directly to MySQL

During migration, `slams-mobile/` may still contain mirrored CodeIgniter files. Treat those as temporary until production is fully redeployed from `slams/`.
