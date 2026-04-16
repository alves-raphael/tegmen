# Schema
- id 
- policy_number ← insurer's number 
- customer_id 
- vehicle_id 
- insurer_id
- status ← enum (ACTIVE, RENEWED, CANCELLED, EXPIRED) 
- start_date 
- end_date
- premium (decimal 8,2)
- commission_percentage (nullable)
- commission_value ← calculated (nullable)
- renewed_from_id (nullable) (fk itself)
- unique (insurer_id, policy_number)
- notes (text, nullable)
- cancelled_at

# Listing

- Listing grid should contain:
	- Customer name
	- Brand
	- Model
	- Insurer
	- Status
	- Expiration date
	- Premium
	- Days until expiration
	- Cancellation button (red)
	- Renewal link
- The UI should follow something similar to this:
![[policy-sample-screenshot.png]] or @policy-sample-screenshot.png
- Status should be a "badge": green for "active", red for "cancelled" or "expired", and blue for "renewed"
- "Days until expiration" should also be a badge, changing color depending on the count:
	  - Green: +30 days
	  - Yellow: > 15 days && <= 30 days
	  - Red: <= 15 days
- Have a status filter, with the default status = active on entry
- Have sorting by expiration date, default = closest to expiration
- The cancellation button should open a modal asking the user to confirm the cancellation; upon confirmation, close the modal and reload the page
- The renewal link is just the link to the new policy registration page + a query string with the policy id

# Registration

- The status field will not be present, but the database default is always "active"
- Customer, insurer, and vehicle will be combo boxes with a search-by-name option
	- The vehicle combo box is only enabled after the customer has been selected and will be filtered based on that selection
- Date fields must have a mask in the format DD/MM/YYYY
- Upon successful registration, the user should be redirected to the listing and a success toast should be displayed
- If registration fails on the server, an error message should be displayed in a toast, without redirection
- The end date must be validated to ensure its value is greater than the current date
- The premium field must have a currency mask with the value in R$
- The premium field should be displayed before commission_percentage
- commission_percentage should be displayed before commission_value
- commission_percentage must only allow numbers from 0 to 100
- When filling in commission_percentage, commission_value should be automatically calculated based on the premium
- If a query string with a policy id is present, the corresponding record must be fetched from the database and used to pre-fill the form, and renewed_from_id must be set to that id
- If renewed_from_id is present in the POST request, it must be validated that the customer and vehicle match the original policy; if not, return a generic error to be displayed in a toast. This event must be logged.
