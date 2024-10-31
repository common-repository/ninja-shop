# Telemetry

Basic configuration and usage data is passed to api.getninjashop.com.

## Opt-in, Opt-out

The opt-in/opt-out system uses two separate settings for allowing different levels of consent.

By default, opt-in and opt-out are both disabled, which allows for the default state to be modified by another plugin.

`add_filter( 'ninja_shop_telemetry_is_opted_in', '__return_true' );`

However, the default state set by another plugin can be overridden by specifically setting the opt-out.

Example of a fresh installation:

- If the customer has not opted-in...
- If the customer has not opted-out (specifically)...
- Then the customer is treated as opted-out (by default).

Example of a Setup Wizard opt-in:

- If the customer has opted-in (by an option in the Setup Wizard)...
- If the customer has not opted-out (specifically)...
- Then the customer is treated as opted-in.

Example with Connect installed:

- If the customer has not opted-in...
- If the customer has Connect installed and active...
- If the customer has not opted-out (specifically)...
- Then the customer is treated as opted-in (as set by Connect).

Example with Connect installed, but opted-out (specifically):

- If the customer has not opted-in...
- If the customer has Connect installed and active...
- If the customer has (specifically) opted-out...
- Then the customer is treated as opted-out (regardless of Connect).
