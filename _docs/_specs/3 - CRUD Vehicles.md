## Vehicle  
- id  
- customer_id  
- license_plate  
- brand  
- model  
- model_year  
- fipe (nullable)
- usage (personal, rideshare, work)
- color
	- white  
	- black  
	- silver  
	- gray  
	- blue  
	- red  
	- other

### Requirements
#### Listing
- Must have a title like "Vehicles of {Customer Name} / {customer email}"
- The GRID should only contain the model, brand, license plate, and an icon/link for editing
- The route needs to be `/customer/{customer_id}/cars`

#### Registration
- The "license_plate" field must have a mask with formats available in Brazil
- The customer_id must come from the URL
- Non-nullable fields must have a mandatory validation on both the frontend and the backend

#### Editing
- The editing form must follow all the rules of the creation form, only sending the customer id to be edited
- A backend validation is required to verify whether the customer id belongs to the logged-in user. If not, this event must be logged with the attempted value
- The "usage" and "color" fields are predefined with hard-coded values in the backend; only "color" can have values different from the default
