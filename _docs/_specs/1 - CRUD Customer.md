## Customer  
- id  
- name  
- cpf  
- email  
- phone  
- birth_date  

## Address
- street
- zip_code
- neighborhood
- state
- city
- number (varchar)
- complement (Nullable)
- customer_id
- status (boolean)

## Requirements
### Listing
- The listing should only contain the customer's name, email, a button for editing, and a button for cars (which leads to the customer's car listing, with the route `/customer/{customer_id}/cars`) in each record. The buttons are icon-only with a link and a tooltip with a description
- There should be a button, outside the customer GRID, to create a new customer

### Registration
- The registration form must have 2 steps, separated by a "next" button:
	- Essential customer data
	- Address
- All fields require mandatory validation (except nullables) and validation of their respective type (CPF, email, etc.)
- Name must have a minimum of 2 characters validation
- The birth date field must have a date mask in the format dd/MM/YYYY
- The Phone field must have a mask including area code and allowing both landline and mobile phone formats
- The CPF and zip code fields must also have masks
- Upon successful registration, the user should be redirected to the listing, followed by a toast message confirming success
- If the registration could not be completed due to a server error, the user should not be redirected — only a toast with an error message should be shown
- All validations must be present on both the frontend and the backend

### Editing
- The editing form must follow all the rules of the creation form, only sending the id of the customer to be edited
- A backend validation is required to verify whether the customer id belongs to the logged-in user. If not, this event must be logged.
  - Every address change must generate a new record, deactivating the old one and activating the new one in the "status" column
