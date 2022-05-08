-- Add index for CType to speed up number of used elements lookup --
CREATE TABLE tt_content (
	KEY CType (CType),
);
