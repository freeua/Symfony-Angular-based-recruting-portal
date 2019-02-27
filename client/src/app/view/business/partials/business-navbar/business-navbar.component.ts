import { AfterContentInit, Component, OnInit, ViewChild } from '@angular/core';
import { AuthService } from '../../../../services/auth.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { CookieService } from 'ngx-cookie-service';
import { BusinessService } from '../../../../services/business.service';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-business-navbar',
  templateUrl: './business-navbar.component.html',
  styleUrls: ['./business-navbar.component.scss']
})
export class BusinessNavbarComponent implements OnInit, AfterContentInit {

  public modalActiveClose: any;

  @ViewChild('steps') private _steps;

  constructor(
    private readonly _authService: AuthService,
    private readonly _modalService: NgbModal,
    private readonly _businessService: BusinessService,
    private readonly _cookieService: CookieService,
    private readonly _sharedService: SharedService
  ) { }

  ngOnInit() {
  }

  ngAfterContentInit() {
    if (this._cookieService.get('firstPopUp_' + Number(localStorage.getItem('id'))) !== 'true') {
      this.getStatusFirstPopup();
    }
  }

  /**
   * Get status first popup
   * @return {Promise<void>}
   */
  public async getStatusFirstPopup(): Promise<void> {
    try {
      const result = await this._businessService.getStatusFirstPopup();
      if(result.firstPopUp === true){
        this._cookieService.set('firstPopUp_' + Number(localStorage.getItem('id')), 'true', 365, '/');
      }
      else{
          setTimeout(() => {
              this.openVerticallyCentered(this._steps);
          }, 1000);
      }
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  public openSidebar(): void {
    this._sharedService.checkSidebar = true;
  }

  /**
   * opens popup
   * @param content
   */
  public openVerticallyCentered(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, size: 'lg', windowClass: 'xlModal' });
  }

  /**
   * logs user out
   * @returns void
   */
  public logout(): void {
    this._authService.logout();
  }

}
